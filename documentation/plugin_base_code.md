# Código Base do Plugin Google Drive

Nesta fase, a estrutura básica do plugin foi criada e os arquivos essenciais para o backend foram desenvolvidos. Abaixo, um resumo dos arquivos criados e suas funcionalidades:

## Estrutura de Diretórios:

```
openmediavault-googledrive/
├── debian/
│   ├── compat
│   ├── control
│   ├── changelog
│   └── rules
├── etc/
│   └── openmediavault/
│       └── conf.d/
└── usr/
    └── share/
        └── openmediavault/
            ├── engined/
            │   └── rpc/
            │       ├── GoogleDrive.php
            │       └── GoogleDriveAuth.php
            └── workbench/
                ├── component/
                ├── dashboard/
                ├── navigation/
                └── route/
```

## Arquivos Debian:

-   **`debian/control`**: Define os metadados do pacote Debian, como nome, seção, prioridade, mantenedor, dependências e descrição.
-   **`debian/rules`**: Script de construção do pacote Debian, responsável por instalar os arquivos nos diretórios corretos do sistema.
-   **`debian/compat`**: Define o nível de compatibilidade do debhelper.
-   **`debian/changelog`**: Registra as mudanças no pacote.

## Arquivos PHP do Backend (`usr/share/openmediavault/engined/rpc/`):

-   **`GoogleDrive.php`**: Este é o principal arquivo de serviço RPC do plugin. Ele estende `OMV\Rpc\OMVModuleRpcAbstract` e contém os métodos que serão chamados pela interface web do OpenMediaVault. Inclui:
    -   `__construct()`: Inicializa a classe e a autenticação do Google Drive.
    -   `getTestMessage()`: Um método de teste simples para verificar a comunicação.
    -   `getAuthUrl()`: Retorna a URL de autenticação OAuth 2.0 do Google Drive.
    -   `setAuthCode($authCode)`: Autentica o usuário com o código de autorização fornecido.
    -   `isAuthenticated()`: Verifica se o plugin está autenticado com o Google Drive.
    -   `listFiles()`: Lista os arquivos do Google Drive.
    -   `downloadFile($fileId, $outputPath)`: Baixa um arquivo do Google Drive.
    -   `uploadFile($filePath, $fileName, $parentId)`: Faz upload de um arquivo para o Google Drive.
    -   `deleteFile($fileId)`: Exclui um arquivo do Google Drive.

-   **`GoogleDriveAuth.php`**: Uma classe auxiliar responsável por gerenciar o processo de autenticação OAuth 2.0 com a Google Drive API. Ela encapsula a lógica de obtenção da URL de autenticação, troca do código de autorização por um token de acesso, e persistência/atualização do token. Utiliza a biblioteca `google/apiclient`.

## Próximos Passos:

Na próxima fase, será necessário implementar a interface web (frontend) para interagir com esses serviços RPC do backend, permitindo que o usuário configure o plugin, autentique-se com o Google Drive e gerencie seus arquivos através da interface do OpenMediaVault.

