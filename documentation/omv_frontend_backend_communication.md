# Comunicação Frontend-Backend no OpenMediaVault

A interface web do OpenMediaVault (frontend) é desenvolvida em TypeScript utilizando os frameworks Angular e Angular Material, sendo servida pelo Nginx. O backend é composto por código PHP que executa diversas tarefas.

## Elementos Essenciais:

- **Frontend**: Desenvolvido em TypeScript com Angular e Angular Material, servido pelo Nginx.
- **Backend**: Código PHP responsável pelas operações e lógica de negócio.
- **`config.xml`**: Um banco de dados em formato XML localizado em `/etc/openmediavault`, que armazena as configurações do sistema.
- **`omv-salt`**: Uma ferramenta utilizada para implantar configurações e serviços.
- **`omv-engined`**: Um daemon RPC (Remote Procedure Call) que executa o código PHP do backend. O Nginx se conecta a este daemon via socket PHP FastCGI.
- **Listeners**: Código PHP de backend que monitora e reage a mudanças no banco de dados. Esses listeners estão localizados em `/usr/share/openmediavault/engined/modules`.
- **`dirtymodules.json`**: Um arquivo JSON em `/var/lib/openmediavault/dirtymodules.json` que lista as seções que precisam ser reconfiguradas após alterações no banco de dados.

## Fluxo de Comunicação Frontend-Backend:

Quando um usuário interage com a interface web (por exemplo, clica em um botão ou salva uma configuração), o seguinte processo ocorre:

1.  **Chamada RPC (Frontend para Backend)**: O código JavaScript no frontend (Angular) faz uma chamada RPC para o `omv-engined`. Esta chamada inclui o nome do serviço e o método a ser executado no backend.
    - Exemplo de payload JSON enviado ao `omv-engined`:
        ```json
        {
            "service":"SMB",
            "method":"getSettings",
            "params":null,
            "options":null
        }
        ```

2.  **Processamento no Backend**: O `omv-engined` recebe a chamada RPC, executa o método PHP correspondente e interage com o `config.xml` ou outros componentes do sistema.

3.  **Resposta RPC (Backend para Frontend)**: O `omv-engined` retorna uma resposta JSON para o frontend, contendo o resultado da operação.
    - Exemplo de resposta JSON:
        ```json
        {
            "response":{
                "enable":true,
                "workgroup":"HOME",
                "serverstring":"%h server",
                "loglevel":0,
                "usesendfile":true,
                "aio":true,
                "nullpasswords":false,
                "localmaster":false,
                "timeserver":false,
                "winssupport":false,
                "winsserver":"",
                "homesenable":false,
                "homesbrowseable":true,
                "extraoptions":""
            },
            "error":null
        }
        ```

## Implicações para o Desenvolvimento do Plugin Google Drive:

Para o plugin do Google Drive, será necessário:

- **Definir Serviços Backend**: Criar serviços PHP no backend que interajam com a API do Google Drive para autenticação, montagem, sincronização, etc.
- **Desenvolver Componentes Frontend**: Criar componentes Angular/TypeScript que façam chamadas RPC para esses serviços backend e exibam as informações relevantes na interface do usuário.
- **Gerenciamento de Configurações**: Utilizar o `config.xml` para armazenar as configurações específicas do plugin do Google Drive (credenciais, diretórios de sincronização, etc.).
- **Empacotamento**: Garantir que todos os arquivos (YAML, PHP, TypeScript/Angular, etc.) sejam colocados nos diretórios corretos para que o OpenMediaVault possa reconhecer e carregar o plugin.

