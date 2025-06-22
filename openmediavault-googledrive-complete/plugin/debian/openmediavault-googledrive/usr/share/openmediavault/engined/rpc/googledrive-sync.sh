#!/bin/bash

# Script de backup e sincronização automática para Google Drive
# Este script pode ser executado via cron para sincronização periódica

CONFIG_DIR="/etc/openmediavault/googledrive"
CONFIG_FILE="$CONFIG_DIR/config.json"
LOG_FILE="/var/log/googledrive-sync.log"
LOCK_FILE="/var/run/googledrive-sync.lock"

# Função para logging
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Função para verificar se outro processo de sync está rodando
check_lock() {
    if [ -f "$LOCK_FILE" ]; then
        local pid=$(cat "$LOCK_FILE")
        if ps -p "$pid" > /dev/null 2>&1; then
            log_message "AVISO: Processo de sincronização já está rodando (PID: $pid)"
            exit 1
        else
            log_message "Removendo arquivo de lock órfão"
            rm -f "$LOCK_FILE"
        fi
    fi
}

# Função para criar lock
create_lock() {
    echo $$ > "$LOCK_FILE"
}

# Função para remover lock
remove_lock() {
    rm -f "$LOCK_FILE"
}

# Função para ler configuração
read_config() {
    if [ ! -f "$CONFIG_FILE" ]; then
        log_message "ERRO: Arquivo de configuração não encontrado: $CONFIG_FILE"
        exit 1
    fi
    
    # Verificar se a sincronização automática está habilitada
    local auto_sync=$(jq -r '.auto_sync // false' "$CONFIG_FILE" 2>/dev/null)
    if [ "$auto_sync" != "true" ]; then
        log_message "Sincronização automática está desabilitada"
        exit 0
    fi
}

# Função para sincronizar diretório
sync_directory() {
    local local_dir="$1"
    local remote_dir="$2"
    local direction="$3"  # up, down, both
    
    log_message "Sincronizando: $local_dir <-> $remote_dir (direção: $direction)"
    
    case "$direction" in
        "up")
            # Upload: local -> Google Drive
            if command -v rclone >/dev/null 2>&1; then
                rclone sync "$local_dir" "googledrive:$remote_dir" \
                    --config "$CONFIG_DIR/rclone.conf" \
                    --log-file "$LOG_FILE" \
                    --log-level INFO \
                    --stats 1m
            else
                log_message "ERRO: rclone não encontrado para upload"
                return 1
            fi
            ;;
        "down")
            # Download: Google Drive -> local
            if command -v rclone >/dev/null 2>&1; then
                rclone sync "googledrive:$remote_dir" "$local_dir" \
                    --config "$CONFIG_DIR/rclone.conf" \
                    --log-file "$LOG_FILE" \
                    --log-level INFO \
                    --stats 1m
            else
                log_message "ERRO: rclone não encontrado para download"
                return 1
            fi
            ;;
        "both")
            # Sincronização bidirecional (cuidado com conflitos)
            log_message "AVISO: Sincronização bidirecional pode causar conflitos"
            if command -v rclone >/dev/null 2>&1; then
                # Primeiro, sincronizar mudanças locais para o Drive
                rclone sync "$local_dir" "googledrive:$remote_dir" \
                    --config "$CONFIG_DIR/rclone.conf" \
                    --log-file "$LOG_FILE" \
                    --log-level INFO \
                    --stats 1m
                
                # Depois, sincronizar mudanças do Drive para local
                rclone sync "googledrive:$remote_dir" "$local_dir" \
                    --config "$CONFIG_DIR/rclone.conf" \
                    --log-file "$LOG_FILE" \
                    --log-level INFO \
                    --stats 1m
            else
                log_message "ERRO: rclone não encontrado para sincronização bidirecional"
                return 1
            fi
            ;;
        *)
            log_message "ERRO: Direção de sincronização inválida: $direction"
            return 1
            ;;
    esac
    
    return $?
}

# Função para backup completo
backup_directories() {
    log_message "Iniciando backup automático"
    
    # Ler diretórios de sincronização da configuração
    local sync_dirs=$(jq -r '.sync_directories[]? | @base64' "$CONFIG_FILE" 2>/dev/null)
    
    if [ -z "$sync_dirs" ]; then
        log_message "Nenhum diretório configurado para sincronização"
        return 0
    fi
    
    local success_count=0
    local error_count=0
    
    while IFS= read -r encoded_dir; do
        if [ -n "$encoded_dir" ]; then
            local dir_config=$(echo "$encoded_dir" | base64 -d)
            local local_path=$(echo "$dir_config" | jq -r '.local_path')
            local remote_path=$(echo "$dir_config" | jq -r '.remote_path')
            local direction=$(echo "$dir_config" | jq -r '.direction // "up"')
            
            if [ -d "$local_path" ]; then
                if sync_directory "$local_path" "$remote_path" "$direction"; then
                    ((success_count++))
                    log_message "Sucesso: $local_path"
                else
                    ((error_count++))
                    log_message "ERRO: Falha na sincronização de $local_path"
                fi
            else
                log_message "AVISO: Diretório local não existe: $local_path"
            fi
        fi
    done <<< "$sync_dirs"
    
    log_message "Backup concluído: $success_count sucessos, $error_count erros"
    return $error_count
}

# Função para limpeza de logs antigos
cleanup_logs() {
    # Manter apenas os últimos 30 dias de logs
    find "$(dirname "$LOG_FILE")" -name "$(basename "$LOG_FILE")*" -mtime +30 -delete 2>/dev/null || true
}

# Função principal
main() {
    log_message "=== Iniciando sincronização automática do Google Drive ==="
    
    # Verificar dependências
    if ! command -v jq >/dev/null 2>&1; then
        log_message "ERRO: jq não está instalado (necessário para ler configuração JSON)"
        exit 1
    fi
    
    # Verificar lock
    check_lock
    
    # Criar lock
    create_lock
    
    # Configurar trap para remover lock em caso de interrupção
    trap remove_lock EXIT
    
    # Ler configuração
    read_config
    
    # Executar backup
    backup_directories
    local exit_code=$?
    
    # Limpeza
    cleanup_logs
    
    log_message "=== Sincronização automática concluída ==="
    
    exit $exit_code
}

# Verificar argumentos
case "${1:-}" in
    "")
        # Execução normal
        main
        ;;
    "test")
        # Modo de teste
        echo "Modo de teste - verificando configuração..."
        read_config
        echo "Configuração válida!"
        ;;
    "help"|"--help"|"-h")
        echo "Uso: $0 [test|help]"
        echo ""
        echo "Opções:"
        echo "  (sem argumentos) - Executar sincronização"
        echo "  test             - Testar configuração"
        echo "  help             - Mostrar esta ajuda"
        ;;
    *)
        echo "ERRO: Argumento inválido '$1'"
        echo "Use '$0 help' para ver as opções disponíveis"
        exit 1
        ;;
esac

