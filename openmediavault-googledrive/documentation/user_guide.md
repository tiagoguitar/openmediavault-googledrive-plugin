# Guia do Usuário - Plugin Google Drive para OpenMediaVault

## Índice

1. [Introdução](#introdução)
2. [Primeiros Passos](#primeiros-passos)
3. [Gerenciamento de Arquivos](#gerenciamento-de-arquivos)
4. [Casos de Uso Práticos](#casos-de-uso-práticos)
5. [Configurações Avançadas](#configurações-avançadas)
6. [Dicas e Truques](#dicas-e-truques)
7. [Perguntas Frequentes](#perguntas-frequentes)

## Introdução

O plugin Google Drive para OpenMediaVault permite integrar seu servidor de mídia doméstico com o Google Drive, oferecendo:

- **Backup Automático**: Sincronize automaticamente seus arquivos importantes
- **Acesso Remoto**: Acesse arquivos do servidor através do Google Drive
- **Expansão de Armazenamento**: Use o Google Drive como extensão do seu armazenamento local
- **Compartilhamento**: Compartilhe arquivos facilmente através do Google Drive

### Benefícios Principais

- ✅ Interface integrada ao OpenMediaVault
- ✅ Sincronização bidirecional
- ✅ Montagem como sistema de arquivos
- ✅ Backup automático programável
- ✅ Gerenciamento através da web

## Primeiros Passos

### Após a Instalação

1. **Acesse a Interface**
   - Abra a interface web do OpenMediaVault
   - Vá para "Services" > "Google Drive"

2. **Configure a Autenticação**
   - Clique na aba "Settings"
   - Siga o processo de autenticação OAuth 2.0
   - Autorize o acesso ao seu Google Drive

3. **Verifique a Conexão**
   - Vá para a aba "Files"
   - Você deve ver seus arquivos do Google Drive listados

### Interface do Plugin

#### Aba Settings (Configurações)
- **Authenticate with Google Drive**: Inicia o processo de autenticação
- **Authorization Code**: Campo para inserir o código de autorização
- **Status**: Mostra se está autenticado ou não

#### Aba Files (Arquivos)
- **Lista de Arquivos**: Exibe arquivos do Google Drive
- **Ações**: Download e exclusão de arquivos
- **Busca**: Encontre arquivos rapidamente
- **Atualização**: Lista é atualizada automaticamente

## Gerenciamento de Arquivos

### Visualizar Arquivos

A aba "Files" mostra uma tabela com:
- **Nome**: Nome do arquivo ou pasta
- **Tipo**: Tipo MIME do arquivo
- **Tamanho**: Tamanho em bytes (formatado automaticamente)
- **Modificado**: Data da última modificação

### Download de Arquivos

1. **Selecionar Arquivo**
   - Clique em um arquivo na lista para selecioná-lo
   - O arquivo selecionado ficará destacado

2. **Iniciar Download**
   - Clique no ícone de download (⬇️)
   - O arquivo será baixado para `/tmp/` no servidor

3. **Localizar Arquivo**
   ```bash
   # Verificar downloads
   ls -la /tmp/nome_do_arquivo
   
   # Mover para local desejado
   sudo mv /tmp/nome_do_arquivo /srv/dev-disk-by-uuid-xxx/downloads/
   ```

### Exclusão de Arquivos

⚠️ **ATENÇÃO**: A exclusão é permanente!

1. **Selecionar Arquivo**
   - Clique no arquivo que deseja excluir

2. **Confirmar Exclusão**
   - Clique no ícone de lixeira (🗑️)
   - Confirme a ação quando solicitado

3. **Verificar Resultado**
   - O arquivo desaparecerá da lista
   - A exclusão é sincronizada com o Google Drive

## Casos de Uso Práticos

### Caso 1: Backup Automático de Documentos

**Objetivo**: Fazer backup automático da pasta de documentos do usuário.

**Configuração**:
```json
{
    "enabled": true,
    "auto_sync": true,
    "sync_interval": 3600,
    "sync_directories": [
        {
            "local_path": "/srv/dev-disk-by-uuid-xxx/documents",
            "remote_path": "Backup/Documents",
            "direction": "up"
        }
    ]
}
```

**Resultado**: Todos os documentos são automaticamente enviados para a pasta "Backup/Documents" no Google Drive a cada hora.

### Caso 2: Sincronização de Fotos Familiares

**Objetivo**: Manter fotos sincronizadas entre o servidor e o Google Drive.

**Configuração**:
```json
{
    "enabled": true,
    "auto_sync": true,
    "sync_interval": 1800,
    "sync_directories": [
        {
            "local_path": "/srv/dev-disk-by-uuid-xxx/photos/family",
            "remote_path": "Photos/Family",
            "direction": "both"
        }
    ]
}
```

**Resultado**: Fotos adicionadas em qualquer local (servidor ou Google Drive) são sincronizadas automaticamente a cada 30 minutos.

### Caso 3: Arquivo de Mídia para Streaming

**Objetivo**: Usar Google Drive como extensão de armazenamento para mídia.

**Configuração**:
```bash
# Montar Google Drive
sudo /usr/share/openmediavault/engined/rpc/googledrive-mount.sh mount

# Verificar montagem
ls -la /mnt/googledrive/Movies/
```

**Uso**:
- Configure o Plex/Jellyfin para incluir `/mnt/googledrive/Movies/`
- Acesse filmes armazenados no Google Drive como se fossem locais

### Caso 4: Backup de Configurações do Sistema

**Objetivo**: Backup automático das configurações importantes do servidor.

**Script Personalizado**:
```bash
#!/bin/bash
# backup-configs.sh

# Criar backup das configurações
sudo tar -czf /tmp/omv-backup-$(date +%Y%m%d).tar.gz \
    /etc/openmediavault/ \
    /etc/samba/ \
    /etc/nginx/

# Mover para pasta sincronizada
sudo mv /tmp/omv-backup-*.tar.gz /srv/dev-disk-by-uuid-xxx/backups/

# A sincronização automática enviará para o Google Drive
```

**Cron**:
```bash
# Backup semanal aos domingos às 2h
0 2 * * 0 /usr/local/bin/backup-configs.sh
```

### Caso 5: Compartilhamento de Arquivos Temporários

**Objetivo**: Compartilhar arquivos grandes temporariamente.

**Processo**:
1. **Upload via Interface Web**
   - Use a funcionalidade de upload (quando implementada)
   - Ou copie arquivos para pasta sincronizada

2. **Compartilhar no Google Drive**
   - Acesse drive.google.com
   - Clique com botão direito no arquivo
   - Selecione "Compartilhar" > "Obter link"

3. **Limpeza Automática**
   ```bash
   # Script para limpar arquivos antigos
   find /srv/dev-disk-by-uuid-xxx/temp-share/ -mtime +7 -delete
   ```

## Configurações Avançadas

### Montagem FUSE Personalizada

**Configuração Avançada do rclone**:
```bash
# Editar configuração do rclone
sudo nano /etc/openmediavault/googledrive/rclone.conf

# Adicionar configurações personalizadas
[googledrive]
type = drive
client_id = seu_client_id
client_secret = seu_client_secret
scope = drive
token = {"access_token":"..."}
team_drive = 
```

**Opções de Montagem**:
```bash
# Montagem com cache agressivo
rclone mount googledrive: /mnt/googledrive \
    --daemon \
    --allow-other \
    --vfs-cache-mode full \
    --vfs-cache-max-age 24h \
    --vfs-cache-max-size 50G \
    --buffer-size 256M
```

### Sincronização Seletiva

**Filtros de Arquivo**:
```bash
# Criar arquivo de filtros
sudo nano /etc/openmediavault/googledrive/filters.txt

# Conteúdo do arquivo
+ *.jpg
+ *.png
+ *.mp4
+ *.mkv
- *.tmp
- *.log
- .DS_Store
```

**Usar Filtros na Sincronização**:
```bash
rclone sync /local/path googledrive:remote/path \
    --filter-from /etc/openmediavault/googledrive/filters.txt
```

### Monitoramento Avançado

**Script de Monitoramento**:
```bash
#!/bin/bash
# monitor-googledrive.sh

# Verificar montagem
if ! mount | grep -q googledrive; then
    echo "ALERTA: Google Drive não está montado"
    # Tentar remontar
    /usr/share/openmediavault/engined/rpc/googledrive-mount.sh mount
fi

# Verificar espaço
USAGE=$(df /mnt/googledrive | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $USAGE -gt 90 ]; then
    echo "ALERTA: Google Drive com pouco espaço: ${USAGE}%"
fi

# Verificar conectividade
if ! curl -s https://www.googleapis.com/drive/v3/about > /dev/null; then
    echo "ALERTA: Sem conectividade com Google Drive API"
fi
```

## Dicas e Truques

### Otimização de Performance

1. **Cache Local**
   - Configure cache local para arquivos frequentemente acessados
   - Use SSD para cache quando possível

2. **Bandwidth Limiting**
   ```bash
   # Limitar largura de banda do rclone
   rclone sync /local googledrive:remote --bwlimit 10M
   ```

3. **Transferências Paralelas**
   ```bash
   # Aumentar transferências paralelas
   rclone sync /local googledrive:remote --transfers 8
   ```

### Organização de Arquivos

1. **Estrutura Recomendada**
   ```
   Google Drive/
   ├── Backup/
   │   ├── Documents/
   │   ├── Photos/
   │   └── Configs/
   ├── Media/
   │   ├── Movies/
   │   ├── TV Shows/
   │   └── Music/
   └── Temp/
   ```

2. **Nomenclatura Consistente**
   - Use datas no formato YYYY-MM-DD
   - Evite caracteres especiais
   - Mantenha nomes descritivos

### Automação com Scripts

**Script de Backup Inteligente**:
```bash
#!/bin/bash
# smart-backup.sh

SOURCE="/srv/dev-disk-by-uuid-xxx/important"
DEST="googledrive:Backup/Important"
LOG="/var/log/smart-backup.log"

# Verificar se há mudanças
if rclone check "$SOURCE" "$DEST" --one-way 2>/dev/null; then
    echo "$(date): Nenhuma mudança detectada" >> "$LOG"
else
    echo "$(date): Iniciando backup..." >> "$LOG"
    rclone sync "$SOURCE" "$DEST" --progress >> "$LOG" 2>&1
    echo "$(date): Backup concluído" >> "$LOG"
fi
```

## Perguntas Frequentes

### Q: O plugin funciona com Google Workspace (G Suite)?
**R**: Sim, o plugin é compatível com contas Google Workspace. Certifique-se de que o administrador da organização permite aplicativos de terceiros.

### Q: Posso usar múltiplas contas Google Drive?
**R**: Atualmente, o plugin suporta apenas uma conta por instalação. Para múltiplas contas, você precisaria de múltiplas instâncias.

### Q: Qual é o limite de armazenamento?
**R**: O limite é determinado pela sua conta Google Drive (15GB gratuito, ou conforme seu plano pago).

### Q: Os arquivos são criptografados?
**R**: Os arquivos são transmitidos via HTTPS, mas não são criptografados no Google Drive além da criptografia padrão do Google.

### Q: Posso acessar arquivos offline?
**R**: Com a montagem FUSE e cache local, alguns arquivos podem estar disponíveis offline temporariamente.

### Q: Como fazer backup da configuração do plugin?
**R**: 
```bash
sudo tar -czf googledrive-backup.tar.gz /etc/openmediavault/googledrive/
```

### Q: O plugin afeta a performance do OpenMediaVault?
**R**: O impacto é mínimo. A sincronização usa recursos apenas durante as operações ativas.

### Q: Posso pausar a sincronização temporariamente?
**R**: Sim, edite `/etc/openmediavault/googledrive/config.json` e defina `"auto_sync": false`.

### Q: Como verificar se a sincronização está funcionando?
**R**: 
```bash
# Verificar logs
sudo tail -f /var/log/googledrive-sync.log

# Testar manualmente
sudo /usr/share/openmediavault/engined/rpc/googledrive-sync.sh test
```

### Q: Posso sincronizar com pastas compartilhadas?
**R**: Sim, desde que você tenha permissões adequadas na pasta compartilhada.

---

**Versão do Documento**: 1.0  
**Data**: 22 de junho de 2025  
**Plugin**: OpenMediaVault Google Drive v0.1.0

## Suporte

Para suporte adicional:
- Consulte os logs em `/var/log/googledrive-*.log`
- Verifique a documentação técnica
- Reporte problemas no repositório GitHub

