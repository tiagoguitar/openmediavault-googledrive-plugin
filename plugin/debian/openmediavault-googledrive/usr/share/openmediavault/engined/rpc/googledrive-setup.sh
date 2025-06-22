#!/bin/bash

# Script de configuração inicial do plugin Google Drive
# Este script deve ser executado após a instalação do plugin

PLUGIN_CONFIG_DIR="/etc/openmediavault/googledrive"
PLUGIN_NAME="Google Drive"

echo "=== Configuração Inicial do Plugin $PLUGIN_NAME ==="

# Verificar se o diretório de configuração existe
if [ ! -d "$PLUGIN_CONFIG_DIR" ]; then
    echo "Criando diretório de configuração..."
    mkdir -p "$PLUGIN_CONFIG_DIR"
    chmod 755 "$PLUGIN_CONFIG_DIR"
fi

# Criar arquivo de configuração padrão se não existir
CONFIG_FILE="$PLUGIN_CONFIG_DIR/config.json"
if [ ! -f "$CONFIG_FILE" ]; then
    echo "Criando arquivo de configuração padrão..."
    cat > "$CONFIG_FILE" << EOF
{
    "enabled": false,
    "auto_sync": false,
    "sync_interval": 3600,
    "mount_enabled": false,
    "mount_point": "/mnt/googledrive",
    "sync_directories": []
}
EOF
    chmod 644 "$CONFIG_FILE"
fi

# Verificar dependências
echo "Verificando dependências..."

# Verificar PHP
if ! command -v php >/dev/null 2>&1; then
    echo "ERRO: PHP não encontrado. Por favor, instale o PHP."
    exit 1
fi

# Verificar Composer
if ! command -v composer >/dev/null 2>&1; then
    echo "AVISO: Composer não encontrado. A biblioteca Google API PHP Client deve ser instalada manualmente."
else
    echo "Composer encontrado: $(composer --version)"
fi

# Verificar rclone (opcional)
if command -v rclone >/dev/null 2>&1; then
    echo "rclone encontrado: $(rclone version | head -n1)"
else
    echo "AVISO: rclone não encontrado. Funcionalidade de montagem FUSE não estará disponível."
fi

# Criar ponto de montagem padrão
MOUNT_POINT="/mnt/googledrive"
if [ ! -d "$MOUNT_POINT" ]; then
    echo "Criando ponto de montagem padrão: $MOUNT_POINT"
    mkdir -p "$MOUNT_POINT"
    chmod 755 "$MOUNT_POINT"
fi

# Verificar permissões do OpenMediaVault
if command -v omv-confdbadm >/dev/null 2>&1; then
    echo "OpenMediaVault detectado. Verificando configuração..."
    # Tentar ler a configuração do plugin
    if omv-confdbadm read conf.service.googledrive >/dev/null 2>&1; then
        echo "Configuração do plugin encontrada no banco de dados do OMV."
    else
        echo "Criando configuração padrão no banco de dados do OMV..."
        omv-confdbadm create conf.service.googledrive || echo "AVISO: Falha ao criar configuração no OMV."
    fi
else
    echo "AVISO: OpenMediaVault não detectado. Este plugin foi projetado para funcionar com o OMV."
fi

echo ""
echo "=== Configuração Inicial Concluída ==="
echo ""
echo "Próximos passos:"
echo "1. Acesse a interface web do OpenMediaVault"
echo "2. Navegue para Services > Google Drive"
echo "3. Configure a autenticação OAuth 2.0"
echo "4. Faça upload do arquivo client_secret.json do Google Cloud Console"
echo ""
echo "Para mais informações, consulte a documentação do plugin."

exit 0

