# Scripts de Backend do Plugin Google Drive

Nesta fase, foram implementados diversos scripts de backend para gerenciar a instalação, configuração e operação do plugin Google Drive. Abaixo está um resumo dos scripts criados:

## Scripts Debian (debian/)

### 1. `postinst` - Script de Pós-Instalação
- **Função**: Executado após a instalação do pacote
- **Responsabilidades**:
  - Criar diretório de configuração (`/etc/openmediavault/googledrive`)
  - Instalar dependências PHP via Composer (Google API Client)
  - Instalar rclone para montagem FUSE
  - Criar configuração padrão no banco de dados do OMV
  - Recarregar o daemon do OpenMediaVault

### 2. `prerm` - Script de Pré-Remoção
- **Função**: Executado antes da remoção do pacote
- **Responsabilidades**:
  - Desmontar montagens FUSE ativas
  - Finalizar processos relacionados ao rclone

### 3. `postrm` - Script de Pós-Remoção
- **Função**: Executado após a remoção do pacote
- **Responsabilidades**:
  - Remover configurações (modo purge)
  - Limpar banco de dados do OMV
  - Recarregar daemon do OpenMediaVault

## Scripts de Operação (usr/share/openmediavault/engined/rpc/)

### 4. `googledrive-setup.sh` - Configuração Inicial
- **Função**: Script de configuração inicial do plugin
- **Responsabilidades**:
  - Verificar dependências (PHP, Composer, rclone)
  - Criar arquivo de configuração padrão
  - Criar ponto de montagem padrão
  - Verificar integração com OpenMediaVault
  - Fornecer instruções para o usuário

### 5. `googledrive-mount.sh` - Gerenciamento de Montagem FUSE
- **Função**: Gerenciar montagem/desmontagem do Google Drive via rclone
- **Comandos Disponíveis**:
  - `setup`: Configurar rclone para Google Drive
  - `mount`: Montar Google Drive em `/mnt/googledrive`
  - `unmount`: Desmontar Google Drive
  - `status`: Verificar status da montagem
- **Características**:
  - Montagem em daemon com cache VFS
  - Logs detalhados
  - Tratamento de erros robusto
  - Desmontagem forçada quando necessário

### 6. `googledrive-sync.sh` - Sincronização Automática
- **Função**: Script para backup e sincronização automática
- **Características**:
  - Leitura de configuração JSON
  - Sincronização bidirecional, upload ou download
  - Sistema de lock para evitar execuções simultâneas
  - Logs detalhados com rotação automática
  - Suporte a múltiplos diretórios
  - Modo de teste para validação de configuração

## Configuração JSON

O arquivo de configuração padrão (`/etc/openmediavault/googledrive/config.json`) inclui:

```json
{
    "enabled": false,
    "auto_sync": false,
    "sync_interval": 3600,
    "mount_enabled": false,
    "mount_point": "/mnt/googledrive",
    "sync_directories": []
}
```

## Dependências Gerenciadas

- **Google API PHP Client**: Instalado via Composer
- **rclone**: Instalado via script oficial
- **jq**: Necessário para processamento JSON
- **FUSE**: Para montagem de sistemas de arquivos

## Logs e Monitoramento

- **Logs de montagem**: `/var/log/googledrive-mount.log`
- **Logs de sincronização**: `/var/log/googledrive-sync.log`
- **Rotação automática**: Logs mantidos por 30 dias

## Integração com Cron

O script de sincronização pode ser configurado no cron para execução automática:

```bash
# Sincronização a cada hora
0 * * * * /usr/share/openmediavault/engined/rpc/googledrive-sync.sh
```

## Segurança

- Arquivos de configuração com permissões restritivas
- Sistema de lock para evitar conflitos
- Validação de dependências antes da execução
- Tratamento de erros robusto

Estes scripts fornecem uma base sólida para a operação do plugin, permitindo instalação automatizada, configuração flexível e operação confiável do Google Drive no OpenMediaVault.

