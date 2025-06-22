# Estrutura do Repositório OneDrive

O repositório `onedrive` clonado possui a seguinte estrutura de diretórios e arquivos:

- `LICENSE`: Arquivo de licença do projeto.
- `Makefile.in`: Arquivo de configuração para o `make`.
- `aclocal.m4`: Arquivo gerado pelo `aclocal` para o sistema de build `Autotools`.
- `changelog.md`: Histórico de mudanças do projeto.
- `config/`: Contém arquivos de configuração.
- `configure`: Script de configuração gerado pelo `Autotools`.
- `configure.ac`: Arquivo de entrada para o `autoconf`.
- `contrib/`: Contém contribuições de terceiros ou scripts auxiliares.
- `docs/`: Documentação do projeto.
- `install-sh`: Script de instalação.
- `onedrive.1.in`: Página de manual do `onedrive`.
- `readme.md`: Arquivo README do projeto.
- `src/`: **Diretório principal do código fonte.** Este é o diretório mais importante para análise, pois contém a lógica da aplicação.
- `tests/`: Contém os testes do projeto.

## Foco da Análise:

O diretório `src/` será o foco principal da análise para entender como o cliente OneDrive interage com a API do OneDrive, como ele lida com a autenticação, sincronização de arquivos e outras operações. Isso será crucial para adaptar a lógica para o Google Drive.

