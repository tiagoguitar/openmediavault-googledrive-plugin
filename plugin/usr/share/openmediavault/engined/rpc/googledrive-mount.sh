#!/bin/bash

# Script para montagem FUSE do Google Drive usando rclone
# Este script gerencia a montagem e desmontagem do Google Drive

MOUNT_POINT="/mnt/googledrive"
RCLONE_CONFIG_DIR="/etc/openmediavault/googledrive"
RCLONE_CONFIG_FILE="$RCLONE_CONFIG_DIR/rclone.conf"
REMOTE_NAME="googledrive"

# Função para verificar se rclone está instalado
check_rclone() {
    if ! command -v rclone >/dev/null 2>&1; then
        echo "ERRO: rclone não está instalado."
        echo "Para instalar: curl https://rclone.org/install.sh | bash"
        exit 1
    fi
}

# Função para configurar rclone
setup_rclone() {
    echo "Configurando rclone para Google Drive..."
    
    if [ ! -d "$RCLONE_CONFIG_DIR" ]; then
        mkdir -p "$RCLONE_CONFIG_DIR"
    fi
    
    # Configurar rclone interativamente
    RCLONE_CONFIG="$RCLONE_CONFIG_FILE" rclone config create "$REMOTE_NAME" drive
    
    if [ $? -eq 0 ]; then
        echo "rclone configurado com sucesso!"
    else
        echo "ERRO: Falha na configuração do rclone."
        exit 1
    fi
}

# Função para montar Google Drive
mount_drive() {
    echo "Montando Google Drive em $MOUNT_POINT..."
    
    # Verificar se já está montado
    if mount | grep -q "$MOUNT_POINT"; then
        echo "Google Drive já está montado em $MOUNT_POINT"
        return 0
    fi
    
    # Criar ponto de montagem se não existir
    if [ ! -d "$MOUNT_POINT" ]; then
        mkdir -p "$MOUNT_POINT"
    fi
    
    # Montar usando rclone
    RCLONE_CONFIG="$RCLONE_CONFIG_FILE" rclone mount "$REMOTE_NAME:" "$MOUNT_POINT" \
        --daemon \
        --allow-other \
        --vfs-cache-mode writes \
        --vfs-cache-max-age 100h \
        --vfs-cache-max-size 10G \
        --log-file /var/log/googledrive-mount.log \
        --log-level INFO
    
    # Verificar se a montagem foi bem-sucedida
    sleep 2
    if mount | grep -q "$MOUNT_POINT"; then
        echo "Google Drive montado com sucesso em $MOUNT_POINT"
        return 0
    else
        echo "ERRO: Falha ao montar Google Drive"
        return 1
    fi
}

# Função para desmontar Google Drive
unmount_drive() {
    echo "Desmontando Google Drive de $MOUNT_POINT..."
    
    # Verificar se está montado
    if ! mount | grep -q "$MOUNT_POINT"; then
        echo "Google Drive não está montado em $MOUNT_POINT"
        return 0
    fi
    
    # Tentar desmontagem normal
    if umount "$MOUNT_POINT" 2>/dev/null; then
        echo "Google Drive desmontado com sucesso"
        return 0
    fi
    
    # Tentar desmontagem forçada
    echo "Tentando desmontagem forçada..."
    if umount -l "$MOUNT_POINT" 2>/dev/null; then
        echo "Google Drive desmontado com sucesso (forçado)"
        return 0
    fi
    
    # Matar processos rclone se necessário
    echo "Finalizando processos rclone..."
    pkill -f "rclone mount.*$REMOTE_NAME"
    sleep 2
    
    # Tentar desmontagem novamente
    if umount "$MOUNT_POINT" 2>/dev/null; then
        echo "Google Drive desmontado com sucesso"
        return 0
    else
        echo "ERRO: Falha ao desmontar Google Drive"
        return 1
    fi
}

# Função para verificar status da montagem
status_drive() {
    if mount | grep -q "$MOUNT_POINT"; then
        echo "Google Drive está montado em $MOUNT_POINT"
        echo "Processos rclone ativos:"
        ps aux | grep -v grep | grep "rclone mount.*$REMOTE_NAME" || echo "Nenhum processo rclone encontrado"
    else
        echo "Google Drive não está montado"
    fi
}

# Função para mostrar ajuda
show_help() {
    echo "Uso: $0 {setup|mount|unmount|status|help}"
    echo ""
    echo "Comandos:"
    echo "  setup    - Configurar rclone para Google Drive"
    echo "  mount    - Montar Google Drive"
    echo "  unmount  - Desmontar Google Drive"
    echo "  status   - Verificar status da montagem"
    echo "  help     - Mostrar esta ajuda"
}

# Verificar argumentos
case "$1" in
    setup)
        check_rclone
        setup_rclone
        ;;
    mount)
        check_rclone
        mount_drive
        ;;
    unmount)
        unmount_drive
        ;;
    status)
        status_drive
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        echo "ERRO: Comando inválido '$1'"
        show_help
        exit 1
        ;;
esac

exit $?

