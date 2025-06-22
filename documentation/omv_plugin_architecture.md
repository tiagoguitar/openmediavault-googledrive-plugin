# Arquitetura de Plugins do OpenMediaVault

Plugins no OpenMediaVault são implementados de forma declarativa, utilizando arquivos YAML para definir seus componentes. Não é necessário conhecimento de JavaScript ou TypeScript para o desenvolvimento de plugins.

## Estrutura de Diretórios de um Plugin:

Os arquivos YAML de um plugin devem estar localizados no diretório `/usr/share/openmediavault/workbench/` e seus subdiretórios, cada um com um significado específico:

- **component.d**: Contém os arquivos de manifesto das páginas exibidas na interface web do OpenMediaVault.
  - Exemplo de manifesto `component.d`:
    ```yaml
    version: "1.0"
    type: component
    data:
      name: omv-services-clamav-onaccess-scan-form-page
      type: formPage
      config:
        request:
          service: ClamAV
          get:
            method: getOnAccessPath
            params:
              uuid: "{{ _routeParams.uuid }}"
          post:
            method: setOnAccessPath
            fields:
              - type: confObjUuid
                type: checkbox
                name: enable
                label: _("Enabled")
                value: false
              - type: sharedFolderSelect
                name: sharedFolderref
                label: _("Shared folder")
                hint: _("The location of the files to scan on-access.")
                validators:
                  required: true
            buttons:
              - template: submit
                execute:
                  type: url
                  url: "/services/clamav/onaccess-scans"
              - template: cancel
                execute:
                  type: url
                  url: "/services/clamav/onaccess-scans"
    ```

- **dashboard.d**: Contém os arquivos de manifesto dos widgets do painel de controle.
  - Tipos de widgets disponíveis: `grid`, `datatable`, `rrd`, `chart`, `text`, `value`.
  - Exemplo de manifesto `dashboard.d`:
    ```yaml
    version: "1.0"
    type: dashboard-widget
    data:
      id: 9984d6cc-741b-4fda-85bf-fc6471a61e97
      permissions:
        role:
          - admin
      title: _("CPU Usage")
      type: chart
      chart:
        type: gauge
        min: 0
        max: 100
        displayValue: true
        request:
          service: System
          method: getInformation
          label: Usage
        formatter: template
        formatterConfig: "{{ value | tofixed(1) }}%"
        dataConfig:
          - label: Usage
            prop: cpuUsage
            backgroundColor: "#4cd964"
    ```

- **log.d**: Contém os arquivos de manifesto usados para configurar o conteúdo do log que é exibido na interface web.
  - Exemplo de manifesto `log.d`:
    ```yaml
    version: "1.0"
    type: log
    data:
      id: clamav
      text: _("Antivirus")
      columns:
        - name: _("Date & Time")
          sortable: true
          prop: ts
          cellTemplateName: localeDateTime
          flexGrow: 1
        - name: _("Message")
          sortable: true
          prop: message
          flexGrow: 2
      request:
        service: LogFile
        method: getList
        params:
          id: clamav
    ```

- **navigation.d**: Contém os arquivos de manifesto usados para configurar a barra de navegação no lado esquerdo da interface web.
  - Exemplo de manifesto `navigation.d`:
    ```yaml
    version: "1.0"
    type: navigation-item
    data:
      path: "services.clamav.onaccess-scans"
      text: _("On Access Scans")
      position: 20
      icon: "mdi:file-eye"
    ```

- **route.d**: Contém os arquivos de manifesto usados para configurar as rotas da interface web.

## Tipos de Páginas Suportados:

- `blankPage`
- `codeEditorPage`
- `formPage`
- `selectionListPage`
- `textPage`
- `tabsPage`
- `datatablePage`
- `rrdPage`

As propriedades disponíveis para cada tipo de página podem ser encontradas nos modelos correspondentes na documentação.

