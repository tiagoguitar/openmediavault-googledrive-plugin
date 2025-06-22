# Interface Web do Plugin Google Drive

Nesta fase, a interface web do plugin foi implementada utilizando os arquivos YAML do OpenMediaVault Workbench. A interface é composta por três tipos de arquivos YAML:

## Arquivos YAML Criados:

### 1. Navegação (`navigation/googledrive.yaml`)

Este arquivo adiciona o item "Google Drive" ao menu de navegação do OpenMediaVault:
- **Texto**: "Google Drive"
- **Ícone**: "mdi:google-drive" (ícone do Material Design)
- **URL**: "/services/googledrive"
- **Posição**: 50 (define a ordem no menu)

### 2. Rota (`route/googledrive.yaml`)

Define a rota da interface web:
- **URL**: "/services/googledrive"
- **Título**: "Google Drive"
- **Componente**: "omv-services-googledrive-page"

### 3. Componentes (`component/googledrive.yaml`)

Este arquivo contém três componentes principais:

#### a) `omv-services-googledrive-page`
- **Tipo**: "tabsPage" (página com abas)
- **Abas**:
  - "Settings": Para configuração e autenticação
  - "Files": Para gerenciamento de arquivos

#### b) `omv-services-googledrive-settings-tab`
- **Tipo**: "formPage" (página de formulário)
- **Funcionalidades**:
  - Verifica se está autenticado (`isAuthenticated`)
  - Botão para obter URL de autenticação (`getAuthUrl`)
  - Campo de entrada para código de autorização
  - Botão para definir código de autorização (`setAuthCode`)

#### c) `omv-services-googledrive-files-tab`
- **Tipo**: "datatable" (tabela de dados)
- **Funcionalidades**:
  - Lista arquivos do Google Drive (`listFiles`)
  - Colunas: Nome, Tipo, Tamanho, Data de Modificação
  - Ações: Download e Delete de arquivos
  - Busca e ordenação automáticas

## Fluxo de Uso da Interface:

1. **Acesso**: O usuário acessa "Services" > "Google Drive" no menu do OpenMediaVault.
2. **Configuração**: Na aba "Settings", o usuário:
   - Clica em "Authenticate with Google Drive" para obter a URL de autenticação
   - Acessa a URL no navegador e autoriza o aplicativo
   - Copia o código de autorização e o cola no campo correspondente
   - Clica em "Set Authorization Code" para completar a autenticação
3. **Gerenciamento**: Na aba "Files", o usuário pode:
   - Visualizar a lista de arquivos do Google Drive
   - Baixar arquivos selecionados
   - Excluir arquivos selecionados
   - Usar a busca para encontrar arquivos específicos

## Comunicação com o Backend:

A interface web faz chamadas RPC para os métodos definidos no arquivo `GoogleDrive.php`:
- `isAuthenticated()`: Verifica o status de autenticação
- `getAuthUrl()`: Obtém a URL de autenticação OAuth 2.0
- `setAuthCode()`: Define o código de autorização
- `listFiles()`: Lista os arquivos do Google Drive
- `downloadFile()`: Baixa um arquivo
- `deleteFile()`: Exclui um arquivo

Esta implementação fornece uma interface completa e intuitiva para os usuários gerenciarem sua integração com o Google Drive através do OpenMediaVault.

