# Documentação Técnica - Plugin Google Drive para OpenMediaVault

## Índice

1. [Arquitetura do Plugin](#arquitetura-do-plugin)
2. [Estrutura de Arquivos](#estrutura-de-arquivos)
3. [Backend (PHP)](#backend-php)
4. [Frontend (YAML)](#frontend-yaml)
5. [Scripts de Sistema](#scripts-de-sistema)
6. [API e Integrações](#api-e-integrações)
7. [Desenvolvimento e Contribuição](#desenvolvimento-e-contribuição)
8. [Testes e Debugging](#testes-e-debugging)

## Arquitetura do Plugin

### Visão Geral

O plugin segue a arquitetura padrão do OpenMediaVault, dividindo-se em três camadas principais:

```
┌─────────────────────────────────────────┐
│           Frontend (Web UI)             │
│        YAML Components/Routes           │
└─────────────────┬───────────────────────┘
                  │ HTTP/RPC
┌─────────────────▼───────────────────────┐
│            Backend (PHP)                │
│         RPC Services/Classes            │
└─────────────────┬───────────────────────┘
                  │ Shell/API
┌─────────────────▼───────────────────────┐
│         System Scripts (Shell)          │
│      rclone/Google API/File System      │
└─────────────────────────────────────────┘
```

### Componentes Principais

1. **Interface Web (YAML)**
   - Navegação e rotas
   - Componentes de UI
   - Formulários e tabelas

2. **Serviços RPC (PHP)**
   - Autenticação OAuth 2.0
   - Operações com arquivos
   - Gerenciamento de configuração

3. **Scripts de Sistema (Shell)**
   - Montagem FUSE
   - Sincronização automática
   - Configuração inicial

## Estrutura de Arquivos

### Diretório Completo

```
openmediavault-googledrive/
├── debian/                              # Pacote Debian
│   ├── changelog                        # Histórico de versões
│   ├── compat                          # Compatibilidade debhelper
│   ├── control                         # Metadados do pacote
│   ├── install                         # Lista de arquivos a instalar
│   ├── postinst                        # Script pós-instalação
│   ├── postrm                          # Script pós-remoção
│   ├── prerm                           # Script pré-remoção
│   └── rules                           # Regras de construção
├── etc/
│   └── openmediavault/
│       └── conf.d/                     # Configurações do OMV
├── usr/
│   └── share/
│       └── openmediavault/
│           ├── engined/
│           │   └── rpc/                # Serviços RPC
│           │       ├── GoogleDrive.php
│           │       ├── GoogleDriveAuth.php
│           │       ├── googledrive-mount.sh
│           │       ├── googledrive-setup.sh
│           │       └── googledrive-sync.sh
│           └── workbench/              # Interface Web
│               ├── component/
│               │   └── googledrive.yaml
│               ├── navigation/
│               │   └── googledrive.yaml
│               └── route/
│                   └── googledrive.yaml
├── LICENSE                             # Licença MIT
└── README.md                          # Documentação básica
```

### Arquivos de Configuração

```
/etc/openmediavault/googledrive/
├── client_secret.json                  # Credenciais OAuth (usuário)
├── token.json                          # Tokens de acesso (gerado)
├── config.json                         # Configuração do plugin
└── rclone.conf                         # Configuração do rclone (gerado)
```

## Backend (PHP)

### Classe GoogleDrive (RPC Service)

**Arquivo**: `usr/share/openmediavault/engined/rpc/GoogleDrive.php`

#### Métodos Principais

```php
class GoogleDrive extends OMVModuleRpc
{
    // Configuração e status
    public function getSettings($params, $context);
    public function setSettings($params, $context);
    public function getStatus($params, $context);
    
    // Operações com arquivos
    public function getFiles($params, $context);
    public function downloadFile($params, $context);
    public function deleteFile($params, $context);
    
    // Autenticação
    public function getAuthUrl($params, $context);
    public function setAuthCode($params, $context);
}
```

#### Exemplo de Implementação

```php
public function getFiles($params, $context)
{
    // Validar parâmetros
    $this->validateMethodParams($params, "rpc.googledrive.getfiles");
    
    // Verificar autenticação
    $auth = new GoogleDriveAuth();
    if (!$auth->isAuthenticated()) {
        throw new OMVException(OMVErrorMsg::E_MISC_FAILURE, 
            "Not authenticated with Google Drive");
    }
    
    // Obter serviço Drive
    $service = $auth->getDriveService();
    
    // Listar arquivos
    $results = $service->files->listFiles([
        'pageSize' => 100,
        'fields' => 'files(id,name,mimeType,size,modifiedTime)'
    ]);
    
    // Formatar resposta
    $files = [];
    foreach ($results->getFiles() as $file) {
        $files[] = [
            'id' => $file->getId(),
            'name' => $file->getName(),
            'mimeType' => $file->getMimeType(),
            'size' => $file->getSize(),
            'modifiedTime' => $file->getModifiedTime()
        ];
    }
    
    return $files;
}
```

### Classe GoogleDriveAuth

**Arquivo**: `usr/share/openmediavault/engined/rpc/GoogleDriveAuth.php`

#### Funcionalidades

- Gerenciamento de credenciais OAuth 2.0
- Renovação automática de tokens
- Criação de cliente Google API
- Verificação de status de autenticação

#### Exemplo de Uso

```php
$auth = new GoogleDriveAuth('/etc/openmediavault/googledrive');

// Verificar se está autenticado
if ($auth->isAuthenticated()) {
    $service = $auth->getDriveService();
    // Usar serviço...
} else {
    $authUrl = $auth->getAuthUrl();
    // Redirecionar para autenticação...
}
```

### Tratamento de Erros

```php
try {
    $result = $service->files->get($fileId);
} catch (Google_Service_Exception $e) {
    $error = json_decode($e->getMessage(), true);
    throw new OMVException(OMVErrorMsg::E_MISC_FAILURE, 
        "Google Drive API error: " . $error['error']['message']);
} catch (Exception $e) {
    throw new OMVException(OMVErrorMsg::E_MISC_FAILURE, 
        "Unexpected error: " . $e->getMessage());
}
```

## Frontend (YAML)

### Navegação

**Arquivo**: `usr/share/openmediavault/workbench/navigation/googledrive.yaml`

```yaml
version: "1.0"
type: navigation
data:
  - text: "Google Drive"
    icon: "mdi:google-drive"
    url: "/services/googledrive"
    position: 50
```

### Rotas

**Arquivo**: `usr/share/openmediavault/workbench/route/googledrive.yaml`

```yaml
version: "1.0"
type: route
data:
  - url: "/services/googledrive"
    title: "Google Drive"
    editing: false
    component: "omv-services-googledrive-page"
```

### Componentes

**Arquivo**: `usr/share/openmediavault/workbench/component/googledrive.yaml`

#### Estrutura Principal

```yaml
version: "1.0"
type: component
data:
  name: "omv-services-googledrive-page"
  type: "tabsPage"
  config:
    tabs:
      - title: "Settings"
        type: "formPage"
        config:
          # Configurações de autenticação
      - title: "Files"
        type: "datatablePage"
        config:
          # Lista de arquivos
```

#### Formulário de Configuração

```yaml
fields:
  - type: "textInput"
    name: "authUrl"
    label: "Authentication URL"
    readonly: true
    value: "{{ getAuthUrl() }}"
  - type: "textInput"
    name: "authCode"
    label: "Authorization Code"
    hint: "Paste the authorization code from Google"
  - type: "button"
    text: "Set Authorization Code"
    request:
      service: "GoogleDrive"
      method: "setAuthCode"
```

#### Tabela de Arquivos

```yaml
columns:
  - name: "name"
    prop: "name"
    flexGrow: 1
    sortable: true
  - name: "mimeType"
    prop: "mimeType"
    flexGrow: 1
  - name: "size"
    prop: "size"
    flexGrow: 1
    cellTemplateName: "binaryUnit"
  - name: "modifiedTime"
    prop: "modifiedTime"
    flexGrow: 1
    cellTemplateName: "localeDateTime"
```

## Scripts de Sistema

### Script de Configuração

**Arquivo**: `googledrive-setup.sh`

#### Funcionalidades

- Instalação do Composer
- Instalação da biblioteca Google API PHP Client
- Instalação do rclone
- Criação de diretórios de configuração
- Configuração de permissões

#### Exemplo de Função

```bash
install_composer() {
    if ! command -v composer &> /dev/null; then
        echo "Instalando Composer..."
        curl -sS https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer
        sudo chmod +x /usr/local/bin/composer
        echo "Composer instalado com sucesso"
    else
        echo "Composer já está instalado"
    fi
}
```

### Script de Montagem

**Arquivo**: `googledrive-mount.sh`

#### Comandos Disponíveis

- `setup`: Configurar rclone
- `mount`: Montar Google Drive
- `unmount`: Desmontar Google Drive
- `status`: Verificar status da montagem
- `help`: Mostrar ajuda

#### Exemplo de Montagem

```bash
mount_googledrive() {
    local mount_point="/mnt/googledrive"
    local config_file="/etc/openmediavault/googledrive/rclone.conf"
    
    if mount | grep -q "$mount_point"; then
        echo "Google Drive já está montado"
        return 0
    fi
    
    mkdir -p "$mount_point"
    
    rclone mount googledrive: "$mount_point" \
        --config "$config_file" \
        --daemon \
        --allow-other \
        --vfs-cache-mode writes \
        --log-file /var/log/googledrive-mount.log \
        --log-level INFO
    
    if mount | grep -q "$mount_point"; then
        echo "Google Drive montado com sucesso em $mount_point"
        return 0
    else
        echo "Erro ao montar Google Drive"
        return 1
    fi
}
```

### Script de Sincronização

**Arquivo**: `googledrive-sync.sh`

#### Funcionalidades

- Sincronização bidirecional
- Configuração via JSON
- Logs detalhados
- Tratamento de erros
- Modo de teste

#### Exemplo de Sincronização

```bash
sync_directory() {
    local local_path="$1"
    local remote_path="$2"
    local direction="$3"
    
    case "$direction" in
        "up")
            rclone sync "$local_path" "googledrive:$remote_path" \
                --progress --log-file "$LOG_FILE"
            ;;
        "down")
            rclone sync "googledrive:$remote_path" "$local_path" \
                --progress --log-file "$LOG_FILE"
            ;;
        "both")
            rclone bisync "$local_path" "googledrive:$remote_path" \
                --progress --log-file "$LOG_FILE"
            ;;
    esac
}
```

## API e Integrações

### Google Drive API

#### Endpoints Utilizados

- **Files**: `/drive/v3/files`
  - `GET`: Listar arquivos
  - `GET /{fileId}`: Obter arquivo específico
  - `DELETE /{fileId}`: Excluir arquivo
  - `POST`: Upload de arquivo

- **About**: `/drive/v3/about`
  - `GET`: Informações da conta

#### Autenticação OAuth 2.0

```php
$client = new Google_Client();
$client->setAuthConfig('/path/to/client_secret.json');
$client->addScope(Google_Service_Drive::DRIVE);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Obter URL de autorização
$authUrl = $client->createAuthUrl();

// Trocar código por token
$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
```

### Integração com rclone

#### Configuração Automática

```bash
# Configurar rclone com tokens do plugin
rclone config create googledrive drive \
    client_id="$CLIENT_ID" \
    client_secret="$CLIENT_SECRET" \
    token="$TOKEN_JSON"
```

#### Comandos Principais

```bash
# Listar arquivos
rclone ls googledrive:

# Sincronizar
rclone sync /local/path googledrive:remote/path

# Montar
rclone mount googledrive: /mnt/googledrive
```

## Desenvolvimento e Contribuição

### Ambiente de Desenvolvimento

#### Pré-requisitos

```bash
# Instalar dependências de desenvolvimento
sudo apt-get install -y \
    build-essential \
    debhelper \
    devscripts \
    php-cli \
    composer \
    yamllint
```

#### Estrutura do Projeto

```bash
# Clonar repositório
git clone https://github.com/usuario/openmediavault-googledrive.git
cd openmediavault-googledrive

# Instalar dependências PHP
composer install

# Validar arquivos YAML
yamllint usr/share/openmediavault/workbench/
```

### Construção do Pacote

```bash
# Construir pacote Debian
dpkg-buildpackage -us -uc -b

# Testar instalação
sudo dpkg -i ../openmediavault-googledrive_*.deb
```

### Padrões de Código

#### PHP

- Seguir PSR-12 para formatação
- Usar DocBlocks para documentação
- Implementar tratamento de erros robusto
- Validar todos os parâmetros de entrada

#### YAML

- Usar indentação de 2 espaços
- Manter linhas com máximo 80 caracteres
- Incluir marcador de início de documento (`---`)

#### Shell

- Usar `#!/bin/bash` como shebang
- Implementar `set -e` para parar em erros
- Documentar funções com comentários
- Validar parâmetros de entrada

### Testes

#### Testes Unitários PHP

```php
// Exemplo de teste
class GoogleDriveAuthTest extends PHPUnit\Framework\TestCase
{
    public function testGetAuthUrl()
    {
        $auth = new GoogleDriveAuth('/tmp/test');
        $url = $auth->getAuthUrl();
        
        $this->assertStringContains('accounts.google.com', $url);
        $this->assertStringContains('oauth2', $url);
    }
}
```

#### Testes de Integração

```bash
# Testar scripts
bash -n googledrive-setup.sh
bash -n googledrive-mount.sh
bash -n googledrive-sync.sh

# Testar sintaxe PHP
php -l GoogleDrive.php
php -l GoogleDriveAuth.php

# Testar YAML
yamllint *.yaml
```

## Testes e Debugging

### Logs do Sistema

#### Localizações

```bash
# Logs do OpenMediaVault
/var/log/openmediavault/engined.log

# Logs do plugin
/var/log/googledrive-mount.log
/var/log/googledrive-sync.log

# Logs do sistema
journalctl -u openmediavault-engined
```

#### Configuração de Debug

```php
// Habilitar debug no PHP
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log personalizado
error_log("Debug: " . print_r($data, true), 3, "/tmp/googledrive-debug.log");
```

### Ferramentas de Debug

#### Verificação de Conectividade

```bash
# Testar API do Google
curl -H "Authorization: Bearer $ACCESS_TOKEN" \
     https://www.googleapis.com/drive/v3/about

# Testar rclone
rclone lsd googledrive: --config /path/to/rclone.conf
```

#### Monitoramento de Recursos

```bash
# Processos relacionados
ps aux | grep -E "(rclone|php|googledrive)"

# Uso de memória
free -h

# Uso de disco
df -h /mnt/googledrive
```

### Solução de Problemas Comuns

#### Erro de Autenticação

```bash
# Verificar tokens
cat /etc/openmediavault/googledrive/token.json | jq .

# Renovar tokens
php -r "
$auth = new GoogleDriveAuth('/etc/openmediavault/googledrive');
$auth->refreshToken();
"
```

#### Problemas de Montagem

```bash
# Verificar FUSE
lsmod | grep fuse
sudo modprobe fuse

# Verificar permissões
ls -la /mnt/googledrive
sudo chmod 755 /mnt/googledrive
```

---

**Versão do Documento**: 1.0  
**Data**: 22 de junho de 2025  
**Plugin**: OpenMediaVault Google Drive v0.1.0

## Contribuições

Para contribuir com o projeto:

1. Fork o repositório
2. Crie uma branch para sua feature
3. Implemente mudanças seguindo os padrões
4. Execute todos os testes
5. Submeta um Pull Request

## Licença

Este projeto está licenciado sob a MIT License - veja o arquivo LICENSE para detalhes.

