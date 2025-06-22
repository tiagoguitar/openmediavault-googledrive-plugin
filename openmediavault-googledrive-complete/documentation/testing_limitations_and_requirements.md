# Limitações e Requisitos para Testes em Ambiente Real

## Limitações do Ambiente de Teste Atual

### 1. Ausência do OpenMediaVault
- **Limitação**: O ambiente de teste não possui o OpenMediaVault instalado
- **Impacto**: Não é possível testar a integração completa com o sistema OMV
- **Componentes não testados**:
  - Integração com o banco de dados de configuração do OMV
  - Funcionamento da interface web no contexto real
  - Comunicação RPC entre frontend e backend
  - Recarregamento do daemon openmediavault-engined

### 2. Dependências PHP Ausentes
- **Limitação**: Google API PHP Client não está instalado via Composer
- **Impacto**: Não é possível testar autenticação real com Google Drive API
- **Componentes não testados**:
  - Autenticação OAuth 2.0 real
  - Operações reais com arquivos do Google Drive
  - Gerenciamento de tokens de acesso e refresh

### 3. Configuração de Rede e Firewall
- **Limitação**: Ambiente sandbox com restrições de rede
- **Impacto**: Não é possível testar conectividade externa
- **Componentes não testados**:
  - Acesso à API do Google Drive
  - Download/instalação do rclone
  - Montagem FUSE real

## Requisitos para Testes em Ambiente Real

### 1. Ambiente OpenMediaVault
```bash
# Sistema base necessário
- OpenMediaVault 6.0 ou superior
- Debian 11 (Bullseye) ou Ubuntu 22.04 LTS
- Acesso root/sudo
- Conexão com internet ativa
```

### 2. Dependências do Sistema
```bash
# Instalar dependências necessárias
sudo apt-get update
sudo apt-get install -y php-cli composer curl jq

# Verificar versões
php --version    # >= 7.4
composer --version
```

### 3. Configuração do Google Cloud Console
```
1. Criar projeto no Google Cloud Console
2. Ativar Google Drive API
3. Criar credenciais OAuth 2.0
4. Configurar URIs de redirecionamento
5. Baixar client_secret.json
```

### 4. Preparação do Ambiente de Teste
```bash
# Instalar o plugin
sudo dpkg -i openmediavault-googledrive_0.1.0_all.deb
sudo apt-get install -f  # Resolver dependências

# Verificar instalação
sudo systemctl status openmediavault-engined
ls -la /usr/share/openmediavault/engined/rpc/Google*
```

## Testes Recomendados para Ambiente Real

### 1. Testes de Instalação
- [ ] Instalação do pacote .deb sem erros
- [ ] Criação automática de diretórios de configuração
- [ ] Instalação das dependências PHP via Composer
- [ ] Instalação do rclone
- [ ] Recarregamento do daemon OMV

### 2. Testes de Interface Web
- [ ] Aparição do item "Google Drive" no menu de navegação
- [ ] Carregamento da página de configuração
- [ ] Funcionamento dos botões de autenticação
- [ ] Exibição correta da lista de arquivos
- [ ] Funcionalidade de download/upload

### 3. Testes de Autenticação
- [ ] Geração da URL de autenticação OAuth 2.0
- [ ] Processo completo de autorização
- [ ] Armazenamento seguro dos tokens
- [ ] Renovação automática de tokens expirados
- [ ] Verificação de status de autenticação

### 4. Testes de Operações com Arquivos
- [ ] Listagem de arquivos do Google Drive
- [ ] Download de arquivos para o servidor
- [ ] Upload de arquivos para o Google Drive
- [ ] Exclusão de arquivos
- [ ] Gerenciamento de pastas

### 5. Testes de Montagem FUSE
- [ ] Configuração inicial do rclone
- [ ] Montagem do Google Drive em /mnt/googledrive
- [ ] Acesso aos arquivos via sistema de arquivos
- [ ] Desmontagem segura
- [ ] Tratamento de erros de conectividade

### 6. Testes de Sincronização Automática
- [ ] Configuração de diretórios para sincronização
- [ ] Execução manual do script de sincronização
- [ ] Configuração de tarefas cron
- [ ] Monitoramento de logs
- [ ] Tratamento de conflitos

### 7. Testes de Desempenho
- [ ] Upload/download de arquivos grandes (>100MB)
- [ ] Sincronização de muitos arquivos (>1000)
- [ ] Uso de CPU e memória durante operações
- [ ] Estabilidade em operações prolongadas

### 8. Testes de Segurança
- [ ] Proteção dos tokens de acesso
- [ ] Permissões de arquivos de configuração
- [ ] Validação de entrada de dados
- [ ] Prevenção de ataques de injeção

## Cenários de Teste Específicos

### Cenário 1: Primeira Instalação
1. Instalar plugin em sistema OMV limpo
2. Configurar credenciais do Google Cloud
3. Realizar primeira autenticação
4. Testar operações básicas

### Cenário 2: Atualização do Plugin
1. Instalar versão anterior (simulada)
2. Atualizar para nova versão
3. Verificar migração de configurações
4. Testar funcionalidades existentes

### Cenário 3: Recuperação de Erros
1. Simular falha de conectividade
2. Testar comportamento com tokens expirados
3. Verificar recuperação após reinicialização
4. Testar logs de erro

### Cenário 4: Carga de Trabalho Intensiva
1. Configurar sincronização de múltiplos diretórios
2. Executar operações simultâneas
3. Monitorar recursos do sistema
4. Verificar integridade dos dados

## Ferramentas de Monitoramento Recomendadas

### Logs do Sistema
```bash
# Logs do OpenMediaVault
tail -f /var/log/openmediavault/engined.log

# Logs do plugin
tail -f /var/log/googledrive-mount.log
tail -f /var/log/googledrive-sync.log

# Logs do sistema
journalctl -u openmediavault-engined -f
```

### Monitoramento de Recursos
```bash
# CPU e memória
htop

# Uso de disco
df -h
du -sh /mnt/googledrive

# Processos relacionados
ps aux | grep -E "(rclone|php|googledrive)"
```

### Verificação de Conectividade
```bash
# Teste de conectividade com Google
curl -I https://www.googleapis.com/drive/v3/about

# Verificação de montagem FUSE
mount | grep googledrive
ls -la /mnt/googledrive
```

## Critérios de Aceitação

### Funcionalidade Básica
- ✅ Plugin instala sem erros
- ✅ Interface web carrega corretamente
- ✅ Autenticação OAuth 2.0 funciona
- ✅ Operações básicas com arquivos funcionam

### Estabilidade
- ✅ Sistema permanece estável após 24h de uso
- ✅ Não há vazamentos de memória
- ✅ Recuperação automática de erros temporários

### Usabilidade
- ✅ Interface intuitiva para usuários não técnicos
- ✅ Mensagens de erro claras e úteis
- ✅ Documentação adequada

### Segurança
- ✅ Tokens armazenados com segurança
- ✅ Permissões de arquivo adequadas
- ✅ Validação de entrada robusta

Este documento serve como guia para testes abrangentes em ambiente de produção real.

