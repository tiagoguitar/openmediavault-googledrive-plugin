# Google Drive API

A Google Drive API permite que aplicativos utilizem o armazenamento em nuvem do Google Drive. É uma API REST que possibilita a integração de funcionalidades do Drive em suas aplicações.

## Principais Funcionalidades:

- **Download e Upload de Arquivos**: Permite baixar e enviar arquivos para o Google Drive.
- **Pesquisa de Arquivos e Pastas**: Capacidade de realizar buscas complexas por arquivos e pastas, utilizando metadados.
- **Compartilhamento de Arquivos, Pastas e Drives**: Facilita a colaboração, permitindo que usuários compartilhem conteúdo.
- **Integração com Google Picker API**: Permite pesquisar todos os arquivos no Drive e retornar informações como nome, URL, data da última modificação e usuário.
- **Criação de Atalhos de Terceiros**: Possibilidade de criar links externos para dados armazenados fora do Drive.
- **Armazenamento de Dados Específicos do Aplicativo**: Criação de uma pasta dedicada no Drive para armazenar dados específicos do aplicativo, sem acesso ao conteúdo total do usuário.
- **Integração com a UI do Drive**: Permite que aplicativos habilitados para o Drive se integrem à interface de usuário padrão do Google Drive para criar, organizar, descobrir e compartilhar arquivos.
- **Aplicação de Rótulos**: Permite aplicar rótulos a arquivos do Drive, definir valores de campo de rótulo, ler valores de campo de rótulo em arquivos e pesquisar arquivos usando termos de metadados de rótulo definidos pela taxonomia de rótulo personalizada.

## Autenticação:

A API do Google Drive requer o protocolo de autorização **OAuth 2.0** para autenticar os usuários do aplicativo. Se o aplicativo usar o "Sign In With Google", ele lida com o fluxo OAuth 2.0 e os tokens de acesso do aplicativo.

## Recursos Relacionados:

- **Desenvolvimento com APIs do Google Workspace**: Informações sobre autenticação e autorização.
- **Visão Geral dos Guias de Início Rápido**: Como configurar e executar um aplicativo simples da API do Google Drive.



### Upload de Arquivos

A Google Drive API oferece três tipos de upload de arquivos:

-   **Upload Simples (`uploadType=media`)**: Para arquivos de mídia pequenos (5 MB ou menos) sem metadados. Utiliza o método `files.create`.
-   **Upload Multipart (`uploadType=multipart`)**: Para arquivos pequenos (5 MB ou menos) com metadados em uma única requisição. Também utiliza o método `files.create`.
-   **Upload Resumível (`uploadType=resumable`)**: Para arquivos grandes (maiores que 5 MB) ou em cenários com alta chance de interrupção de rede. Permite retomar o upload após falhas. Inicia com `files.create` para obter um URI de sessão resumível, seguido pelo upload dos dados.

**Uso de PATCH vs. PUT**: `PATCH` para atualização parcial de recursos, `PUT` para substituição completa. Para uploads, `PUT` é usado para requisições subsequentes em uploads resumíveis.

**Tratamento de Erros**: Para erros `5xx`, tentar retomar ou repetir o upload. Para erros `403 rate limit`, repetir o upload. Para outros erros `4xx` em uploads resumíveis, reiniciar a sessão de upload.

**Importar para tipos do Google Docs**: É possível converter arquivos para tipos do Google Workspace (Docs, Sheets, Slides) especificando o `mimeType` apropriado durante a criação do arquivo. O OCR pode ser usado para converter imagens em texto.

**IDs pré-gerados**: A API permite gerar IDs de arquivo (`files.generateIds`) para uploads e criações de recursos, o que facilita a retentativa segura de uploads.

**Texto indexável para tipos de arquivo desconhecidos**: É possível definir texto indexável para tipos de arquivo que o Drive não reconhece automaticamente, para facilitar a busca.



### Download e Exportação de Arquivos

A Google Drive API oferece diversas formas de download e exportação:

-   **Download de Conteúdo Blob (`files.get` com `alt=media`)**: Para baixar o conteúdo de arquivos binários (não-Google Docs). Pode-se usar `Range` header para download parcial.
-   **Download de Versões Anteriores (`revisions.get` com `alt=media`)**: Para baixar o conteúdo de arquivos em versões anteriores.
-   **Download no Navegador (`webContentLink`)**: Permite gerar um link para download direto no navegador.
-   **Download durante Operações de Longa Duração (`files.download`)**: Para arquivos do Google Vids ou outras operações que demoram.
-   **Exportar Conteúdo de Documentos Google Workspace (`files.export`)**: Para exportar documentos Google Workspace (Docs, Sheets, Slides) para outros formatos (ex: PDF, DOCX) usando o `mimeType` correto. Conteúdo exportado limitado a 10 MB.
-   **Exportar no Navegador (`exportLinks`)**: Similar ao download no navegador, mas para documentos Google Workspace.

Antes de baixar ou exportar, é recomendável verificar a capacidade de download do usuário através do campo `capabilities.canDownload` do recurso `files`.



### Exclusão de Arquivos e Pastas

A Google Drive API oferece duas formas de remover arquivos e pastas:

-   **Mover para a Lixeira (`files.update` com `trashed=True`)**: Move o arquivo para a lixeira do Drive, onde ele permanece por 30 dias antes de ser excluído permanentemente. Apenas o proprietário do arquivo pode movê-lo para a lixeira. Para arquivos em drives compartilhados, é necessário definir `supportsAllDrives=True`.
-   **Excluir Permanentemente (`files.delete`)**: Remove o arquivo do Drive de forma definitiva, sem passar pela lixeira. Para arquivos em drives compartilhados, o usuário deve ter `role=organizer` na pasta pai e `supportsAllDrives=True`.

**Esvaziar Lixeira (`files.emptyTrash`)**: Permite excluir permanentemente todos os arquivos que o usuário moveu para a lixeira.

**Permissões e Capacidades**: A API permite verificar as permissões do usuário (`capabilities.canTrash`, `capabilities.canUntrash`, `capabilities.canDelete`) antes de tentar realizar operações de exclusão ou lixeira.



### Montagem de Sistemas de Arquivos (FUSE)

A Google Drive API não oferece uma funcionalidade direta para montar o Google Drive como um sistema de arquivos local. No entanto, a abordagem comum para conseguir isso em sistemas Linux é através do **FUSE (Filesystem in Userspace)**.

Existem projetos de código aberto como `google-drive-ocamlfuse` e `gcsf` (Google Cloud Storage FUSE) que implementam um sistema de arquivos FUSE para o Google Drive. Esses projetos permitem que o conteúdo do Google Drive seja acessado como se fosse um diretório local no sistema de arquivos.

Para o plugin do OpenMediaVault, a estratégia mais viável para a funcionalidade de montagem será:

1.  **Integração com uma Solução FUSE Existente**: A forma mais eficiente seria integrar o plugin com uma ferramenta FUSE já existente e madura para o Google Drive (como `rclone` ou `google-drive-ocamlfuse`). Isso evitaria a necessidade de reimplementar toda a lógica de sistema de arquivos.
2.  **Gerenciamento da Montagem**: O plugin precisaria gerenciar o processo de montagem e desmontagem, bem como a persistência da montagem após reinicializações do sistema.

Isso implica que o plugin do OpenMediaVault precisará ter a capacidade de instalar e configurar uma dessas ferramentas FUSE no sistema subjacente.



### Metadados de Arquivos

A Google Drive API permite gerenciar metadados de arquivos, que são informações descritivas sobre os arquivos, como nome, tipo MIME, tamanho, data de criação/modificação, proprietário, etc.

-   **`files.get`**: Utilizado para obter os metadados de um arquivo específico, usando seu `fileId`. É possível especificar quais campos de metadados devem ser retornados (ex: `id`, `name`, `mimeType`, `size`, `createdTime`, `modifiedTime`, `owners`, `capabilities`).
-   **`files.list`**: Utilizado para listar arquivos e obter seus metadados, com opções de filtragem e busca.
-   **`files.update`**: Permite atualizar os metadados de um arquivo existente.

**Campos Importantes de Metadados:**

-   `id`: Identificador único do arquivo.
-   `name`: Nome do arquivo.
-   `mimeType`: Tipo MIME do arquivo (ex: `image/jpeg`, `application/vnd.google-apps.folder`).
-   `size`: Tamanho do arquivo em bytes.
-   `createdTime`: Data e hora de criação do arquivo.
-   `modifiedTime`: Data e hora da última modificação do arquivo.
-   `parents`: Lista de IDs das pastas pai do arquivo.
-   `ownedByMe`: Booleano indicando se o usuário autenticado é o proprietário do arquivo.
-   `capabilities`: Objeto contendo booleanos que indicam as ações que o usuário pode realizar no arquivo (ex: `canDownload`, `canTrash`, `canDelete`).
-   `thumbnailLink`: URL para uma miniatura do arquivo, se disponível.
-   `fileExtension`: Extensão do arquivo, inferida do nome.
-   `contentHints.indexableText`: Texto que pode ser indexado para melhorar a busca por arquivos de tipos não reconhecidos automaticamente pelo Drive.

O gerenciamento de metadados será crucial para o plugin, permitindo que ele exiba informações sobre os arquivos do Google Drive na interface do OpenMediaVault e realize operações como renomear, mover e pesquisar.

