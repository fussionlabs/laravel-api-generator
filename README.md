# Laravel API Generator with JSON Input

This tool helps to generate **Models**, **Migrations**, **Controllers**, and **Request** files for Laravel APIs, based on a predefined JSON configuration.

## Prerequisites

- **PHP** and **Composer** should be installed.
- A working Laravel project.

## Steps to Use the Laravel API Generator

### 1. Download the API Generator Script

Download or clone the `laravel-api-generator.php` file from the repository and place it in the root directory of your Laravel project.

### 2. Create a JSON Configuration File

You need to define the structure of the tables and the corresponding API behavior in a JSON file. Here's an example structure:

```json
{
  "tables": [
    {
      "name": "Registrations",
      "fields": {
        "name": {
          "type": "string",
          "required": true
        },
        "email": {
          "type": "string",
          "required": true,
          "format": "email"
        },
        "phone_number": {
          "type": "string",
          "required": true
        },
        "dob": {
          "type": "date",
          "required": true
        },
        "gender": {
          "type": "string",
          "required": true
        },
        "address": {
          "type": "string",
          "nullable": true
        },
        "city": {
          "type": "string",
          "nullable": true
        },
        "state": {
          "type": "string",
          "nullable": true
        },
        "country": {
          "type": "string",
          "required": true
        },
        "zip": {
          "type": "string",
          "nullable": true
        }
      },
      "api": {
        "use_middleware": true,
        "middleware": [
          "auth:sanctum"
        ],
        "exclude_methods": [
          "index",
          "show"
        ]
      }
    }
  ]
}
```
#### Explanation of the JSON Structure:

`Tables`: Contains an array of tables that you want to generate.
  `name`: The table name (e.g., `Registrations`).
  `fields`: Defines the fields for the table, their types, and validation rules.
   `` type``: The type of the field (string, date, etc.).
   ` required`: Boolean indicating whether the field is mandatory.
   ` nullable`: Indicates whether the field can be null (if omitted, it's assumed false).
    `format`: Optional format for fields like email.
  `api`: Defines API-related settings. (For api Router)
    `use_middleware`: Boolean to indicate whether middleware should be applied.
    `middleware`: An array of middleware strings (e.g., `auth:sanctum`).
    `exclude_methods`: API methods (like index, show) that should be excluded from the controller.

#### Place the JSON Configuration File in the Project
Save the above JSON structure into a file called input.json or any other name you prefer, and place it in your Laravel project root directory.

#### Run the Generator Script
Open your terminal and navigate to your Laravel project directory:
```bash 
  cd /path/to/your/laravel/project
  ```
```bash
 php laravel-api-generator.php
```
#### Follow the Script Prompts
The script will parse the api-config.json file and generate:
Model for each table.
Migration for each table based on the defined fields.
Controller with the specified middleware (if use_middleware is true).
Request Validation file if validation rules are defined.
Exclude Methods: Any methods defined in exclude_methods will be excluded from the controller.
## How the Generator Works

`Model Creation`: The script generates a model based on the table name.

`Migration Creation`: A migration file is generated based on the fields and their types. For example, string, date, and other data types are mapped to corresponding Laravel schema types.

`Controller Creation`:
If `use_middleware` is true, the specified middleware (e.g., auth:sanctum) is added to the controller.
If `use_middleware` is false, all the basic CRUD methods (index, show, store, update, and destroy) are added without middleware, and methods are excluded as specified in exclude_methods.
