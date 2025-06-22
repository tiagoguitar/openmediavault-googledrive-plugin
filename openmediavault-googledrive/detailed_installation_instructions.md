# Instruções Detalhadas de Instalação - Plugin Google Drive para OpenMediaVault

## Guia Passo a Passo para Instalação

### Pré-requisitos Obrigatórios

Antes de iniciar a instalação, certifique-se de que seu sistema atende aos seguintes requisitos:

#### Sistema Operacional
- ✅ OpenMediaVault 6.0 ou superior instalado e funcionando
- ✅ Debian 11 (Bullseye) ou Ubuntu 22.04 LTS
- ✅ Acesso administrativo (usuário root ou sudo)
- ✅ Conexão estável com a internet

#### Verificação do Sistema
Execute os seguintes comandos para verificar seu sistema:

```bash
# Verificar versão do OpenMediaVault
omv-confdbadm read conf.system.general.version

# Verificar versão do sistema operacional
lsb_release -a

# Verificar conectividade com internet
ping -c 3 google.com

# Verificar espaço em disco (mínimo 500MB livres)
df -h /
```

### Passo 1: Preparação do Google Cloud Console

#### 1.1 Criar Projeto no Google Cloud

1. **Acesse o Google Cloud Console**
   - Vá para: https://console.cloud.google.com/
   - Faça login com sua conta Google

2. **Criar Novo Projeto**
   - Clique no seletor de projeto no topo da página
   - Clique em "NOVO PROJETO"
   - Nome do projeto: `OpenMediaVault-GoogleDrive-[SeuNome]`
   - Clique em "CRIAR"
   - Aguarde a criação do projeto (pode levar alguns minutos)

#### 1.2 Ativar APIs Necessárias

1. **Ativar Google Drive API**
   - No menu lateral, vá para "APIs e serviços" > "Biblioteca"
   - Pesquise por "Google Drive API"
   - Clique na "Google Drive API"
   - Clique em "ATIVAR"
   - Aguarde a ativação (indicador verde aparecerá)

#### 1.3 Configurar Tela de Consentimento OAuth

1. **Acessar Configuração OAuth**
   - Vá para "APIs e serviços" > "Tela de consentimento OAuth"
   - Selecione "Externo" como tipo de usuário
   - Clique em "CRIAR"

2. **Preencher Informações Obrigatórias**
   - Nome do app: `OpenMediaVault Google Drive`
   - E-mail de suporte do usuário: [seu-email@gmail.com]
   - Domínio autorizado: deixe em branco
   - E-mail de contato do desenvolvedor: [seu-email@gmail.com]
   - Clique em "SALVAR E CONTINUAR"

3. **Configurar Escopos**
   - Clique em "ADICIONAR OU REMOVER ESCOPOS"
   - Adicione os seguintes escopos:
     - `https://www.googleapis.com/auth/drive`
     - `https://www.googleapis.com/auth/drive.file`
   - Clique em "ATUALIZAR"
   - Clique em "SALVAR E CONTINUAR"

4. **Adicionar Usuários de Teste**
   - Clique em "ADICIONAR USUÁRIOS"
   - Adicione seu e-mail: [seu-email@gmail.com]
   - Clique em "ADICIONAR"
   - Clique em "SALVAR E CONTINUAR"

#### 1.4 Criar Credenciais OAuth 2.0

1. **Criar ID do Cliente**
   - Vá para "APIs e serviços" > "Credenciais"
   - Clique em "CRIAR CREDENCIAIS" > "ID do cliente OAuth"
   - Tipo de aplicativo: "Aplicativo da Web"
   - Nome: `OpenMediaVault Google Drive Plugin`

2. **Configurar URIs de Redirecionamento**
   - URIs de redirecionamento autorizados:
     - `http://localhost`
     - `http://localhost:8080`
     - `http://[IP-DO-SEU-OMV]`
   - Clique em "CRIAR"

3. **Baixar Credenciais**
   - Uma janela aparecerá com as credenciais
   - Clique em "BAIXAR JSON"
   - Salve o arquivo como `client_secret.json`
   - **IMPORTANTE**: Guarde este arquivo em local seguro

### Passo 2: Download e Preparação dos Arquivos

#### 2.1 Download do Plugin

```bash
# Criar diretório temporário
mkdir -p /tmp/googledrive-install
cd /tmp/googledrive-install

# Baixar o pacote do plugin (substitua pela URL real)
wget https://github.com/seu-usuario/openmediavault-googledrive/releases/download/v0.1.0/openmediavault-googledrive_0.1.0_all.deb

# Verificar integridade do arquivo
ls -la openmediavault-googledrive_0.1.0_all.deb
```

#### 2.2 Preparar Arquivo de Credenciais

```bash
# Copiar arquivo de credenciais para o servidor
# (Use SCP, SFTP ou interface web do OMV para transferir o arquivo)

# Criar diretório de configuração
sudo mkdir -p /etc/openmediavault/googledrive

# Mover arquivo de credenciais
sudo mv /caminho/para/client_secret.json /etc/openmediavault/googledrive/

# Definir permissões corretas
sudo chmod 600 /etc/openmediavault/googledrive/client_secret.json
sudo chown root:root /etc/openmediavault/googledrive/client_secret.json

# Verificar arquivo
sudo ls -la /etc/openmediavault/googledrive/
```

### Passo 3: Instalação do Plugin

#### 3.1 Instalação via Pacote .deb

```bash
# Navegar para o diretório com o pacote
cd /tmp/googledrive-install

# Instalar o pacote
sudo dpkg -i openmediavault-googledrive_0.1.0_all.deb

# Resolver dependências automaticamente
sudo apt-get install -f

# Verificar se a instalação foi bem-sucedida
dpkg -l | grep googledrive
```

#### 3.2 Verificação da Instalação

```bash
# Verificar arquivos instalados
dpkg -L openmediavault-googledrive

# Verificar serviços
sudo systemctl status openmediavault-engined

# Verificar logs
sudo tail -f /var/log/openmediavault/engined.log
```

### Passo 4: Configuração Inicial

#### 4.1 Acessar Interface Web

1. **Abrir Interface do OpenMediaVault**
   - Acesse: `http://[IP-DO-SEU-OMV]`
   - Faça login como administrador

2. **Verificar Menu do Plugin**
   - No menu lateral, vá para "Services"
   - Verifique se "Google Drive" aparece na lista
   - Se não aparecer, recarregue a página (Ctrl+F5)

#### 4.2 Configuração de Dependências

O plugin instalará automaticamente as dependências, mas você pode verificar:

```bash
# Verificar PHP
php --version

# Verificar Composer
composer --version

# Se Composer não estiver instalado:
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Instalar biblioteca Google API PHP Client
cd /usr/share/openmediavault/engined/rpc/
sudo composer require google/apiclient

# Verificar rclone
rclone version

# Se rclone não estiver instalado:
curl https://rclone.org/install.sh | sudo bash
```

### Passo 5: Autenticação com Google Drive

#### 5.1 Iniciar Processo de Autenticação

1. **Acessar Configurações do Plugin**
   - Na interface web do OMV, vá para "Services" > "Google Drive"
   - Clique na aba "Settings"

2. **Obter URL de Autenticação**
   - Clique no botão "Get Authentication URL"
   - Uma URL será exibida no campo "Authentication URL"
   - Copie esta URL

#### 5.2 Autorizar no Google

1. **Abrir URL no Navegador**
   - Cole a URL copiada em um navegador
   - Faça login na sua conta Google (se necessário)

2. **Revisar Permissões**
   - O Google mostrará as permissões solicitadas:
     - "Ver e gerenciar arquivos do Google Drive"
     - "Ver informações sobre arquivos do Google Drive"
   - Clique em "Permitir"

3. **Copiar Código de Autorização**
   - O Google exibirá um código de autorização
   - Copie este código (será algo como: `4/0AX4XfWh...`)

#### 5.3 Completar Autenticação

1. **Inserir Código na Interface**
   - Volte para a interface do OMV
   - Cole o código no campo "Authorization Code"
   - Clique em "Set Authorization Code"

2. **Verificar Autenticação**
   - Aguarde a mensagem de confirmação
   - O status deve mudar para "Authenticated"
   - Vá para a aba "Files" para ver seus arquivos do Google Drive

### Passo 6: Configuração de Funcionalidades Avançadas

#### 6.1 Configurar Montagem FUSE (Opcional)

```bash
# Configurar rclone para montagem
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh setup

# Montar Google Drive
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh mount

# Verificar montagem
ls -la /mnt/googledrive

# Para desmontar
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh unmount
```

#### 6.2 Configurar Sincronização Automática (Opcional)

```bash
# Editar configuração de sincronização
sudo nano /etc/openmediavault/googledrive/config.json

# Exemplo de configuração:
{
    "enabled": true,
    "auto_sync": true,
    "sync_interval": 3600,
    "mount_enabled": true,
    "mount_point": "/mnt/googledrive",
    "sync_directories": [
        {
            "local_path": "/srv/dev-disk-by-uuid-xxx/backup",
            "remote_path": "OMV-Backup",
            "direction": "up"
        }
    ]
}

# Testar sincronização
sudo /usr/share/openmediavault/engined/rpc/googledrive-sync.sh test

# Configurar cron para sincronização automática
sudo crontab -e
# Adicionar linha: 0 * * * * /usr/share/openmediavault/engined/rpc/googledrive-sync.sh
```

### Passo 7: Verificação e Testes

#### 7.1 Testes Básicos

```bash
# Verificar status do plugin
sudo systemctl status openmediavault-engined

# Verificar logs
sudo tail -f /var/log/openmediavault/engined.log

# Testar conectividade com Google Drive API
curl -H "Authorization: Bearer $(cat /etc/openmediavault/googledrive/token.json | jq -r .access_token)" \
     https://www.googleapis.com/drive/v3/about
```

#### 7.2 Teste da Interface Web

1. **Testar Listagem de Arquivos**
   - Vá para "Services" > "Google Drive" > "Files"
   - Verifique se os arquivos do seu Google Drive aparecem

2. **Testar Download**
   - Selecione um arquivo pequeno
   - Clique no ícone de download
   - Verifique se o arquivo foi baixado

3. **Testar Exclusão (Cuidado!)**
   - Crie um arquivo de teste no Google Drive
   - Tente excluí-lo através da interface
   - Verifique se foi removido

### Solução de Problemas Comuns

#### Problema: Plugin não aparece no menu

**Solução:**
```bash
# Recarregar configuração do OMV
sudo omv-confdbadm read conf.system.general

# Reiniciar serviço
sudo systemctl restart openmediavault-engined

# Limpar cache do navegador (Ctrl+Shift+Del)
```

#### Problema: Erro de autenticação

**Solução:**
```bash
# Verificar arquivo de credenciais
sudo cat /etc/openmediavault/googledrive/client_secret.json

# Verificar permissões
sudo ls -la /etc/openmediavault/googledrive/

# Recriar credenciais no Google Cloud Console se necessário
```

#### Problema: Dependências não instaladas

**Solução:**
```bash
# Executar script de configuração manualmente
sudo /usr/share/openmediavault/engined/rpc/googledrive-setup.sh

# Instalar dependências manualmente
sudo apt-get update
sudo apt-get install -y php-cli composer curl jq
```

### Manutenção e Monitoramento

#### Logs Importantes

```bash
# Logs do plugin
sudo tail -f /var/log/googledrive-mount.log
sudo tail -f /var/log/googledrive-sync.log

# Logs do OpenMediaVault
sudo tail -f /var/log/openmediavault/engined.log

# Logs do sistema
journalctl -u openmediavault-engined -f
```

#### Backup da Configuração

```bash
# Fazer backup das configurações
sudo tar -czf googledrive-backup-$(date +%Y%m%d).tar.gz \
    /etc/openmediavault/googledrive/

# Restaurar backup
sudo tar -xzf googledrive-backup-YYYYMMDD.tar.gz -C /
```

### Desinstalação (Se Necessário)

```bash
# Parar sincronização
sudo crontab -e
# Remover linha de sincronização

# Desmontar Google Drive
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh unmount

# Remover plugin
sudo apt-get remove openmediavault-googledrive

# Remover configurações (opcional)
sudo rm -rf /etc/openmediavault/googledrive

# Reiniciar serviço OMV
sudo systemctl restart openmediavault-engined
```

---

## Resumo dos Comandos Principais

### Instalação Rápida
```bash
# 1. Baixar e instalar
wget [URL-DO-PLUGIN]
sudo dpkg -i openmediavault-googledrive_0.1.0_all.deb
sudo apt-get install -f

# 2. Configurar credenciais
sudo mkdir -p /etc/openmediavault/googledrive
sudo mv client_secret.json /etc/openmediavault/googledrive/
sudo chmod 600 /etc/openmediavault/googledrive/client_secret.json

# 3. Acessar interface web e autenticar
# http://[IP-OMV] > Services > Google Drive
```

### Verificação de Status
```bash
# Status geral
sudo systemctl status openmediavault-engined
dpkg -l | grep googledrive

# Logs
sudo tail -f /var/log/openmediavault/engined.log

# Montagem
mount | grep googledrive
ls -la /mnt/googledrive
```

---

**Versão do Documento**: 1.0  
**Data**: 22 de junho de 2025  
**Plugin**: OpenMediaVault Google Drive v0.1.0

**Suporte**: Para problemas ou dúvidas, consulte a documentação técnica ou reporte issues no repositório GitHub.

