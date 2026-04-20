# local_devtools

> **⚠️ Warning: Development Use Only**  
> This plugin is intended for development environments only. Do not use in production as it may cause performance issues and leak database query data.

A collection of tools to help with the development of Moodle.

## Features

- [PHP Debug Bar](https://php-debugbar.com/): A powerful debugging tool that provides insights into database queries, request parameters, and more.

### String Manager Logging

To enable, add the following to `config.php`:

```php
$CFG->customstringmanager = '\local_devtools\local\string_manager';
```

### AJAX Requests Support

To enable, add the following to `/lib/ajax/service.php`:

```php
header('Content-Type: application/json; charset=utf-8');
\local_devtools\local\debugbar::instance()->sendDataInHeaders(); // Add this.
echo json_encode($responses);
```

### CLI

Run `php /path/to/moodle/public/local/devtools/cli/run.php` to view available commands.

### MCP Server

The MCP server provides useful tools for agentic coding workflows.

Add the following configuration to your `mcp.json` file to enable.

```json
{
  "mcpServers": {
    "moodle-devtools": {
      "command": "php",
      "args": ["/path/to/moodle/public/local/devtools/cli/run.php", "mcp:serve"]
    }
  }
}
```
