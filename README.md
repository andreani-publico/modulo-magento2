# Módulo de envíos ANDREANI 2

### Requisitos

Para el correcto funcionamiento del módulo es necesario contar con:

```
Magento version >= 2.1.0 
Extensión WKHTMLToPDF
```

### Instalación

Para concretar la instalacion del módulo es necesario realizar los siguientes pasos:
Pasos de instalación

1. Instalar la extension WKHTMLToPDF mediante el composer.json de magento. Parados en el directorio root de magento, ejecutar en la terminal: 

```
composer require h4cc/wkhtmltopdf-i386 "0.12.3"
```

2. Copiar el archivo Andreani.zip del módulo en app/code/Ids y descomprimirlo ahí.

3. Parados en el directorio root de magento ejecutamos:

```
1. php bin/magento module:enable Ids_Andreani --clear-static-content
2. php bin/magento setup:upgrade
3. rm -rf var/di
4. rm -rf var/view_preprocessed
5. php bin/magento setup:static-content:deploy
```	

## Autores

* **Mauro Maximiliano Martinez ** - *<mmartinez@ids.net.ar>
* **Jhonattan Campo ** - *<jcampo@ids.net.ar>