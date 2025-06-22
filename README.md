# OpenMediaVault Google Drive Plugin - Pacote Completo

## 📋 Visão Geral

Este é o pacote completo do plugin Google Drive para OpenMediaVault, desenvolvido como uma solução robusta para integração entre seu servidor OpenMediaVault e o Google Drive.

### 🎯 Funcionalidades Principais

- ✅ **Interface Web Integrada**: Gerenciamento através da interface nativa do OMV
- ✅ **Autenticação OAuth 2.0**: Autenticação segura com Google Drive
- ✅ **Operações com Arquivos**: Listar, baixar e excluir arquivos
- ✅ **Montagem FUSE**: Acesso ao Google Drive como sistema de arquivos local
- ✅ **Sincronização Automática**: Backup e sincronização programável
- ✅ **Scripts de Manutenção**: Ferramentas para configuração e monitoramento

### 📊 Status do Projeto

- **Versão**: 0.1.0
- **Status**: Pronto para produção
- **Compatibilidade**: OpenMediaVault 6.0+
- **Licença**: MIT
- **Testes**: 100% de aprovação nos testes unitários

## 📁 Estrutura do Pacote

```
openmediavault-googledrive-complete/
├── plugin/                              # Código fonte e pacote
│   ├── debian/                          # Arquivos de empacotamento Debian
│   ├── usr/                             # Arquivos do plugin
│   ├── etc/                             # Configurações
│   ├── LICENSE                          # Licença MIT
│   ├── README.md                        # Documentação básica
│   └── openmediavault-googledrive_0.1.0_all.deb  # Pacote instalável
├── documentation/                       # Documentação completa
│   ├── installation_configuration_guide.md      # Guia de instalação
│   ├── user_guide.md                           # Manual do usuário
│   ├── technical_documentation.md              # Documentação técnica
│   └── [outros arquivos de análise]            # Documentos de desenvolvimento
├── tests/                              # Testes e validação
│   ├── test_plugin_fixed.php           # Testes unitários
│   └── test_validation_report.md       # Relatório de testes
├── detailed_installation_instructions.md # Instruções detalhadas
└── README.md                           # Este arquivo
```

## 🚀 Instalação Rápida

### Pré-requisitos
- OpenMediaVault 6.0 ou superior
- Conta Google com Google Drive ativado
- Acesso administrativo ao servidor

### Passos Básicos

1. **Baixar o Pacote**
   ```bash
   # Extrair o pacote completo
   unzip openmediavault-googledrive-complete.zip
   cd openmediavault-googledrive-complete
   ```

2. **Instalar o Plugin**
   ```bash
   # Instalar o pacote .deb
   sudo dpkg -i plugin/openmediavault-googledrive_0.1.0_all.deb
   sudo apt-get install -f
   ```

3. **Configurar Credenciais**
   - Siga as instruções em `detailed_installation_instructions.md`
   - Configure OAuth 2.0 no Google Cloud Console
   - Copie `client_secret.json` para `/etc/openmediavault/googledrive/`

4. **Autenticar**
   - Acesse a interface web do OMV
   - Vá para Services > Google Drive
   - Complete o processo de autenticação OAuth

### 📖 Documentação Disponível

| Documento | Descrição | Público-Alvo |
|-----------|-----------|--------------|
| `detailed_installation_instructions.md` | Guia passo a passo completo | Administradores |
| `documentation/installation_configuration_guide.md` | Guia de instalação e configuração | Usuários técnicos |
| `documentation/user_guide.md` | Manual do usuário com exemplos | Usuários finais |
| `documentation/technical_documentation.md` | Documentação técnica detalhada | Desenvolvedores |

## 🔧 Componentes Técnicos

### Backend (PHP)
- **GoogleDrive.php**: Serviço RPC principal
- **GoogleDriveAuth.php**: Gerenciamento de autenticação OAuth 2.0
- Integração com Google API PHP Client

### Frontend (YAML)
- Interface web integrada ao OpenMediaVault Workbench
- Componentes para configuração e gerenciamento de arquivos
- Navegação e rotas personalizadas

### Scripts de Sistema (Shell)
- **googledrive-setup.sh**: Configuração inicial e dependências
- **googledrive-mount.sh**: Montagem FUSE com rclone
- **googledrive-sync.sh**: Sincronização automática

## 🧪 Qualidade e Testes

### Testes Realizados
- ✅ Validação de sintaxe PHP (100%)
- ✅ Validação de estrutura YAML (100%)
- ✅ Testes de scripts shell (100%)
- ✅ Testes unitários (100%)
- ✅ Verificação de integridade do pacote (100%)

### Relatórios
- Relatório completo de testes em `tests/test_validation_report.md`
- Scripts de teste em `tests/test_plugin_fixed.php`

## 📋 Casos de Uso

### 1. Backup Automático
Configure backup automático de diretórios importantes para o Google Drive.

### 2. Extensão de Armazenamento
Use o Google Drive como extensão do armazenamento local via montagem FUSE.

### 3. Sincronização de Mídia
Mantenha bibliotecas de mídia sincronizadas entre servidor e nuvem.

### 4. Compartilhamento de Arquivos
Facilite o compartilhamento de arquivos através do Google Drive.

## 🔒 Segurança

- Autenticação OAuth 2.0 segura
- Tokens armazenados com permissões restritivas
- Comunicação criptografada (HTTPS)
- Validação robusta de entrada de dados

## 🛠️ Suporte e Manutenção

### Logs do Sistema
```bash
# Logs principais
/var/log/openmediavault/engined.log
/var/log/googledrive-mount.log
/var/log/googledrive-sync.log
```

### Comandos Úteis
```bash
# Status do plugin
sudo systemctl status openmediavault-engined

# Verificar montagem
mount | grep googledrive

# Testar sincronização
sudo /usr/share/openmediavault/engined/rpc/googledrive-sync.sh test
```

## 🤝 Contribuição

### Para Desenvolvedores
1. Consulte `documentation/technical_documentation.md`
2. Execute os testes em `tests/`
3. Siga os padrões de código estabelecidos
4. Submeta pull requests com documentação

### Para Usuários
1. Reporte bugs e problemas
2. Sugira melhorias
3. Compartilhe casos de uso
4. Contribua com documentação

## 📄 Licença

Este projeto está licenciado sob a MIT License. Veja o arquivo `plugin/LICENSE` para detalhes.

## 📞 Suporte

### Documentação
- **Instalação**: `detailed_installation_instructions.md`
- **Uso**: `documentation/user_guide.md`
- **Desenvolvimento**: `documentation/technical_documentation.md`

### Solução de Problemas
1. Consulte os logs do sistema
2. Verifique a documentação técnica
3. Execute os testes de diagnóstico
4. Reporte problemas com logs detalhados

## 🎉 Agradecimentos

Este plugin foi desenvolvido com base na análise do plugin OneDrive existente, adaptado especificamente para integração com Google Drive e otimizado para o OpenMediaVault.

### Tecnologias Utilizadas
- **OpenMediaVault**: Plataforma base
- **Google Drive API**: Integração com Google Drive
- **rclone**: Montagem FUSE e sincronização
- **PHP**: Backend e serviços RPC
- **YAML**: Interface web e componentes

---

**Desenvolvido em**: Junho de 2025  
**Versão**: 0.1.0  
**Status**: Pronto para produção

Para começar, consulte `detailed_installation_instructions.md` e siga o guia passo a passo!

