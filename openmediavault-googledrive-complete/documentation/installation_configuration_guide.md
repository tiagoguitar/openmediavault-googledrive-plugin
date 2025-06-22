# Guia Completo de Instalação e Configuração - Plugin Google Drive para OpenMediaVault

## Índice

1. [Pré-requisitos](#pré-requisitos)
2. [Preparação do Google Cloud Console](#preparação-do-google-cloud-console)
3. [Instalação do Plugin](#instalação-do-plugin)
4. [Configuração Inicial](#configuração-inicial)
5. [Autenticação com Google Drive](#autenticação-com-google-drive)
6. [Configuração de Funcionalidades Avançadas](#configuração-de-funcionalidades-avançadas)
7. [Solução de Problemas](#solução-de-problemas)
8. [Manutenção e Atualizações](#manutenção-e-atualizações)

## Pré-requisitos

### Sistema Operacional
- OpenMediaVault 6.0 ou superior
- Debian 11 (Bullseye) ou Ubuntu 22.04 LTS
- Acesso administrativo (root/sudo)
- Conexão estável com a internet

### Recursos do Sistema
- **RAM**: Mínimo 512MB disponível
- **Armazenamento**: 100MB livres para o plugin e cache
- **CPU**: Qualquer arquitetura suportada pelo OMV
- **Rede**: Acesso às APIs do Google (porta 443)

### Dependências
O plugin instalará automaticamente as seguintes dependências:
- PHP 7.4 ou superior
- Composer (gerenciador de pacotes PHP)
- Google API PHP Client
- rclone (para montagem FUSE)
- jq (para processamento JSON)

## Preparação do Google Cloud Console

### Passo 1: Criar Projeto no Google Cloud

1. Acesse o [Google Cloud Console](https://console.cloud.google.com/)
2. Faça login com sua conta Google
3. Clique em "Selecionar projeto" no topo da página
4. Clique em "NOVO PROJETO"
5. Digite um nome para o projeto (ex: "OpenMediaVault-GoogleDrive")
6. Clique em "CRIAR"

### Passo 2: Ativar a Google Drive API

1. No painel do projeto, vá para "APIs e serviços" > "Biblioteca"
2. Pesquise por "Google Drive API"
3. Clique na "Google Drive API"
4. Clique em "ATIVAR"

### Passo 3: Criar Credenciais OAuth 2.0

1. Vá para "APIs e serviços" > "Credenciais"
2. Clique em "CRIAR CREDENCIAIS" > "ID do cliente OAuth"
3. Se solicitado, configure a tela de consentimento OAuth:
   - Escolha "Externo" como tipo de usuário
   - Preencha as informações obrigatórias
   - Adicione seu e-mail como usuário de teste
4. Para criar o ID do cliente:
   - Tipo de aplicativo: "Aplicativo da Web"
   - Nome: "OpenMediaVault Google Drive Plugin"
   - URIs de redirecionamento autorizados: `http://localhost`
5. Clique em "CRIAR"
6. **IMPORTANTE**: Baixe o arquivo JSON das credenciais
7. Renomeie o arquivo para `client_secret.json`

## Instalação do Plugin

### Método 1: Instalação via Pacote .deb (Recomendado)

```bash
# 1. Baixar o pacote
wget https://github.com/seu-usuario/openmediavault-googledrive/releases/download/v0.1.0/openmediavault-googledrive_0.1.0_all.deb

# 2. Instalar o pacote
sudo dpkg -i openmediavault-googledrive_0.1.0_all.deb

# 3. Resolver dependências (se necessário)
sudo apt-get install -f

# 4. Verificar instalação
sudo systemctl status openmediavault-engined
```

### Método 2: Instalação Manual

```bash
# 1. Baixar e extrair o código fonte
wget https://github.com/seu-usuario/openmediavault-googledrive/archive/v0.1.0.zip
unzip v0.1.0.zip
cd openmediavault-googledrive-0.1.0

# 2. Copiar arquivos para os diretórios corretos
sudo cp -r usr/* /usr/
sudo cp -r etc/* /etc/

# 3. Definir permissões
sudo chmod +x /usr/share/openmediavault/engined/rpc/googledrive-*.sh

# 4. Executar configuração inicial
sudo /usr/share/openmediavault/engined/rpc/googledrive-setup.sh

# 5. Recarregar daemon do OMV
sudo systemctl reload openmediavault-engined
```

## Configuração Inicial

### Passo 1: Verificar Instalação

1. Acesse a interface web do OpenMediaVault
2. Faça login como administrador
3. Verifique se "Google Drive" aparece no menu "Services"

### Passo 2: Preparar Arquivo de Credenciais

```bash
# 1. Criar diretório de configuração (se não existir)
sudo mkdir -p /etc/openmediavault/googledrive

# 2. Copiar arquivo de credenciais
sudo cp /caminho/para/client_secret.json /etc/openmediavault/googledrive/

# 3. Definir permissões adequadas
sudo chmod 600 /etc/openmediavault/googledrive/client_secret.json
sudo chown root:root /etc/openmediavault/googledrive/client_secret.json
```

### Passo 3: Verificar Dependências

```bash
# Verificar PHP
php --version

# Verificar Composer
composer --version

# Verificar rclone
rclone version

# Verificar jq
jq --version
```

## Autenticação com Google Drive

### Passo 1: Acessar Interface de Configuração

1. Na interface web do OMV, vá para "Services" > "Google Drive"
2. Clique na aba "Settings"

### Passo 2: Iniciar Processo de Autenticação

1. Clique no botão "Authenticate with Google Drive"
2. Uma URL de autenticação será exibida
3. Copie a URL e abra em um navegador

### Passo 3: Autorizar o Aplicativo

1. Faça login na sua conta Google (se necessário)
2. Revise as permissões solicitadas:
   - Ver e gerenciar arquivos do Google Drive
   - Ver informações sobre arquivos do Google Drive
3. Clique em "Permitir"
4. Copie o código de autorização exibido

### Passo 4: Completar Autenticação

1. Volte para a interface do OMV
2. Cole o código de autorização no campo correspondente
3. Clique em "Set Authorization Code"
4. Aguarde a confirmação de sucesso

### Passo 5: Verificar Autenticação

1. Clique na aba "Files"
2. Você deve ver a lista de arquivos do seu Google Drive
3. Se aparecer uma mensagem de erro, verifique os logs

## Configuração de Funcionalidades Avançadas

### Montagem FUSE

A montagem FUSE permite acessar o Google Drive como um sistema de arquivos local.

```bash
# 1. Configurar rclone (primeira vez)
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh setup

# 2. Montar Google Drive
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh mount

# 3. Verificar montagem
ls -la /mnt/googledrive

# 4. Desmontar (quando necessário)
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh unmount
```

### Sincronização Automática

Configure a sincronização automática de diretórios locais com o Google Drive.

#### Configuração Manual

1. Edite o arquivo de configuração:
```bash
sudo nano /etc/openmediavault/googledrive/config.json
```

2. Configure os diretórios para sincronização:
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

#### Configuração do Cron

```bash
# 1. Editar crontab do root
sudo crontab -e

# 2. Adicionar linha para sincronização a cada hora
0 * * * * /usr/share/openmediavault/engined/rpc/googledrive-sync.sh

# 3. Para sincronização a cada 30 minutos
*/30 * * * * /usr/share/openmediavault/engined/rpc/googledrive-sync.sh
```

#### Teste da Sincronização

```bash
# Testar configuração
sudo /usr/share/openmediavault/engined/rpc/googledrive-sync.sh test

# Executar sincronização manual
sudo /usr/share/openmediavault/engined/rpc/googledrive-sync.sh
```

## Solução de Problemas

### Problemas de Instalação

#### Erro: "Package openmediavault is not installed"
```bash
# Forçar instalação ignorando dependências (apenas para teste)
sudo dpkg --force-depends -i openmediavault-googledrive_0.1.0_all.deb
```

#### Erro: "Composer not found"
```bash
# Instalar Composer manualmente
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### Problemas de Autenticação

#### Erro: "Invalid client_secret.json"
1. Verifique se o arquivo está no local correto: `/etc/openmediavault/googledrive/client_secret.json`
2. Verifique as permissões do arquivo: `sudo ls -la /etc/openmediavault/googledrive/`
3. Baixe novamente o arquivo do Google Cloud Console

#### Erro: "Access denied"
1. Verifique se a Google Drive API está ativada
2. Confirme se o URI de redirecionamento está configurado como `http://localhost`
3. Verifique se sua conta está listada como usuário de teste

### Problemas de Montagem FUSE

#### Erro: "rclone not found"
```bash
# Instalar rclone manualmente
curl https://rclone.org/install.sh | sudo bash
```

#### Erro: "FUSE not available"
```bash
# Instalar FUSE
sudo apt-get install fuse

# Verificar se o módulo está carregado
lsmod | grep fuse

# Carregar módulo se necessário
sudo modprobe fuse
```

### Problemas de Sincronização

#### Erro: "jq not found"
```bash
# Instalar jq
sudo apt-get install jq
```

#### Verificar Logs
```bash
# Logs de montagem
sudo tail -f /var/log/googledrive-mount.log

# Logs de sincronização
sudo tail -f /var/log/googledrive-sync.log

# Logs do OpenMediaVault
sudo tail -f /var/log/openmediavault/engined.log
```

## Manutenção e Atualizações

### Backup da Configuração

```bash
# Fazer backup das configurações
sudo tar -czf googledrive-backup-$(date +%Y%m%d).tar.gz /etc/openmediavault/googledrive/

# Restaurar backup
sudo tar -xzf googledrive-backup-YYYYMMDD.tar.gz -C /
```

### Atualização do Plugin

```bash
# 1. Fazer backup da configuração
sudo cp -r /etc/openmediavault/googledrive /tmp/googledrive-backup

# 2. Baixar nova versão
wget https://github.com/seu-usuario/openmediavault-googledrive/releases/download/vX.X.X/openmediavault-googledrive_X.X.X_all.deb

# 3. Instalar atualização
sudo dpkg -i openmediavault-googledrive_X.X.X_all.deb

# 4. Verificar configuração
sudo systemctl status openmediavault-engined
```

### Desinstalação

```bash
# Remover plugin mantendo configurações
sudo apt-get remove openmediavault-googledrive

# Remover plugin e configurações
sudo apt-get purge openmediavault-googledrive

# Limpeza manual (se necessário)
sudo rm -rf /etc/openmediavault/googledrive
sudo umount /mnt/googledrive 2>/dev/null || true
```

### Monitoramento

#### Scripts de Monitoramento

```bash
# Verificar status geral
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh status

# Verificar espaço em disco
df -h /mnt/googledrive

# Verificar processos relacionados
ps aux | grep -E "(rclone|googledrive)"
```

#### Alertas Automáticos

Adicione ao cron para monitoramento automático:

```bash
# Verificar montagem a cada 15 minutos
*/15 * * * * /usr/share/openmediavault/engined/rpc/googledrive-mount.sh status || echo "Google Drive não montado" | mail -s "Alerta OMV" admin@exemplo.com
```

## Considerações de Segurança

### Proteção de Credenciais
- Mantenha o arquivo `client_secret.json` com permissões restritivas (600)
- Não compartilhe credenciais ou tokens de acesso
- Revogue acesso no Google Cloud Console se necessário

### Backup de Tokens
- Tokens são armazenados em `/etc/openmediavault/googledrive/token.json`
- Inclua este arquivo nos backups regulares do sistema
- Tokens expiram e são renovados automaticamente

### Auditoria de Acesso
- Monitore logs regularmente
- Verifique atividade na conta Google Drive
- Configure alertas para atividades suspeitas

---

**Versão do Documento**: 1.0  
**Data**: 22 de junho de 2025  
**Plugin**: OpenMediaVault Google Drive v0.1.0

