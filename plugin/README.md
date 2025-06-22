# OpenMediaVault Google Drive Plugin

Um plugin para OpenMediaVault que permite integração completa com o Google Drive, incluindo autenticação OAuth 2.0, gerenciamento de arquivos, montagem FUSE e sincronização automática.

## Características

- **Autenticação OAuth 2.0**: Integração segura com a API do Google Drive
- **Gerenciamento de Arquivos**: Interface web para listar, baixar, fazer upload e excluir arquivos
- **Montagem FUSE**: Monte seu Google Drive como um sistema de arquivos local usando rclone
- **Sincronização Automática**: Scripts para backup e sincronização automática de diretórios
- **Interface Web Integrada**: Interface nativa do OpenMediaVault para configuração e gerenciamento

## Requisitos

- OpenMediaVault 6.0 ou superior
- PHP 7.4 ou superior
- Composer (para dependências PHP)
- rclone (para montagem FUSE - instalado automaticamente)
- jq (para processamento JSON)

## Instalação

### Método 1: Instalação via Pacote .deb

1. Baixe o arquivo `openmediavault-googledrive_0.1.0_all.deb`
2. Instale o pacote:
   ```bash
   sudo dpkg -i openmediavault-googledrive_0.1.0_all.deb
   sudo apt-get install -f  # Para resolver dependências, se necessário
   ```

### Método 2: Instalação Manual

1. Clone ou baixe o código fonte
2. Copie os arquivos para os diretórios apropriados:
   ```bash
   sudo cp -r usr/* /usr/
   sudo cp -r etc/* /etc/
   sudo chmod +x /usr/share/openmediavault/engined/rpc/googledrive-*.sh
   ```
3. Execute o script de configuração:
   ```bash
   sudo /usr/share/openmediavault/engined/rpc/googledrive-setup.sh
   ```

## Configuração

### 1. Configuração do Google Cloud Console

1. Acesse o [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um novo projeto ou selecione um existente
3. Ative a Google Drive API
4. Crie credenciais OAuth 2.0:
   - Vá para "Credenciais" > "Criar credenciais" > "ID do cliente OAuth"
   - Escolha "Aplicativo da Web"
   - Adicione `http://localhost` como URI de redirecionamento autorizado
5. Baixe o arquivo `client_secret.json`

### 2. Configuração no OpenMediaVault

1. Acesse a interface web do OpenMediaVault
2. Navegue para **Services** > **Google Drive**
3. Na aba **Settings**:
   - Faça upload do arquivo `client_secret.json` para `/etc/openmediavault/googledrive/`
   - Clique em "Authenticate with Google Drive"
   - Siga o link de autenticação e autorize o aplicativo
   - Cole o código de autorização no campo correspondente
   - Clique em "Set Authorization Code"

### 3. Verificação

1. Na aba **Files**, você deve ver a lista de arquivos do seu Google Drive
2. Teste as funcionalidades de download e upload

## Funcionalidades Avançadas

### Montagem FUSE

Para montar seu Google Drive como um sistema de arquivos local:

```bash
# Configurar rclone (apenas na primeira vez)
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh setup

# Montar Google Drive
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh mount

# Verificar status
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh status

# Desmontar
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh unmount
```

O Google Drive será montado em `/mnt/googledrive`.

### Sincronização Automática

Configure a sincronização automática editando `/etc/openmediavault/googledrive/config.json`:

```json
{
    "enabled": true,
    "auto_sync": true,
    "sync_interval": 3600,
    "mount_enabled": true,
    "mount_point": "/mnt/googledrive",
    "sync_directories": [
        {
            "local_path": "/srv/dev-disk-by-uuid-xxx/documents",
            "remote_path": "Backup/Documents",
            "direction": "up"
        },
        {
            "local_path": "/srv/dev-disk-by-uuid-xxx/photos",
            "remote_path": "Backup/Photos",
            "direction": "both"
        }
    ]
}
```

Adicione ao cron para execução automática:

```bash
# Editar crontab
sudo crontab -e

# Adicionar linha para sincronização a cada hora
0 * * * * /usr/share/openmediavault/engined/rpc/googledrive-sync.sh
```

## Solução de Problemas

### Erro de Autenticação

1. Verifique se o arquivo `client_secret.json` está em `/etc/openmediavault/googledrive/`
2. Certifique-se de que a Google Drive API está ativada no Google Cloud Console
3. Verifique se o URI de redirecionamento está configurado corretamente

### Problemas de Montagem FUSE

1. Verifique se o rclone está instalado: `rclone version`
2. Verifique se o FUSE está disponível: `ls /dev/fuse`
3. Verifique os logs: `tail -f /var/log/googledrive-mount.log`

### Problemas de Sincronização

1. Teste a configuração: `/usr/share/openmediavault/engined/rpc/googledrive-sync.sh test`
2. Verifique os logs: `tail -f /var/log/googledrive-sync.log`
3. Verifique se o jq está instalado: `jq --version`

## Logs

- **Montagem FUSE**: `/var/log/googledrive-mount.log`
- **Sincronização**: `/var/log/googledrive-sync.log`
- **OpenMediaVault**: `/var/log/openmediavault/engined.log`

## Desinstalação

```bash
# Remover pacote
sudo apt-get remove openmediavault-googledrive

# Remover configurações (opcional)
sudo apt-get purge openmediavault-googledrive
```

## Suporte

Para problemas e sugestões, abra uma issue no repositório do GitHub.

## Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## Contribuição

Contribuições são bem-vindas! Por favor, leia as diretrizes de contribuição antes de enviar pull requests.

## Changelog

### v0.1.0 (2025-06-22)
- Versão inicial
- Autenticação OAuth 2.0
- Interface web básica
- Montagem FUSE via rclone
- Sincronização automática

