<?php

const colors = [
    'reset' => "\033[0m",  // Reset color
    'black' => "\033[0;30m",
    'red' => "\033[0;31m",
    'green' => "\033[0;32m",
    'yellow' => "\033[0;33m",
    'blue' => "\033[0;34m",
    'magenta' => "\033[0;35m",
    'cyan' => "\033[0;36m",
    'white' => "\033[0;37m",
    'cyan_shade' => "\033[0;5,37m",   
    'light_blue' => "\033[0;5,111m",   
    'blue_shade' => "\033[38;5;68m", 
    'tan_shade'  => "\033[0;16,166m",   
    'yellow_green' => "\033[0;5,185m", 
    'purple_shade' => "\033[0;5,139m", 
	'dark_goldenrod' => "\033[38;5;136m",  // DarkGoldenrod
    'magenta3'       => "\033[38;5;127m",  // Magenta3
    'dodger_blue3'   => "\033[38;5;26m",   // DodgerBlue3
    'light_sea_green'=> "\033[38;5;37m",   // LightSeaGreen
    'dark_orange3'   => "\033[38;5;130m",  // DarkOrange3
    'gold'           => "\033[38;5;220m",
    'bold' => "\033[1m" 
];
const EL = PHP_EOL;

// Paths
$migrationPath = __DIR__ . '/database/migrations/';
$controllerPath = __DIR__ . '/app/Http/Controllers/';
$requestPath = __DIR__ . '/app/Http/Requests/';
$resourcePath = __DIR__ . '/app/Http/Resources/';
$modelFolderPath = __DIR__ . "/app/Models/";
$routePath = __DIR__ . '/routes/api.php';
$responsePath = __DIR__ .'app/Classes/';

checkExistence($migrationPath);
checkExistence($controllerPath);
checkExistence($resourcePath);
checkExistence($requestPath);
checkExistence($modelFolderPath);

function checkExistence($path) {
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    } 
}

// Load JSON input from file
$json = file_get_contents('./input.json');
$input = json_decode($json, true);


// Process the tables

foreach ($input['tables'] as $table) {
    $colors = colors;
    echo "{$colors['yellow']} {$colors['bold']}{$table['name']}: {$colors['reset']}".EL;
    generateMigration($table);
    generateController($table);
    generateRequest($table, 'Store');
    generateRequest($table, 'Update');
    generateResource($table);
    generateModel($table);
    // Add a 1-second delay between generating files
    sleep(1);
}
//APIRESPONSE CLASS

function generateApiResponse(){
    global $responsePath;
    $responseCode ="PD9waHANCg0KbmFtZXNwYWNlIEFwcFxDbGFzc2VzOw0KDQpjbGFzcyBBcGlSZXNwb25zZUNsYXNzDQp7DQogICANCiAgICBwdWJsaWMgc3RhdGljIGZ1bmN0aW9uIHNlbmRSZXNwb25zZSgkcmVzdWx0ICwgJG1lc3NhZ2UgLCRjb2RlPTIwMCl7DQogICAgICAgICRyZXNwb25zZT1bDQogICAgICAgICAgICAnc3VjY2VzcycgPT4gdHJ1ZSwNCiAgICAgICAgICAgICdkYXRhJyAgICA9PiAkcmVzdWx0DQogICAgICAgIF07DQogICAgICAgIGlmKCFlbXB0eSgkbWVzc2FnZSkpew0KICAgICAgICAgICAgJHJlc3BvbnNlWydtZXNzYWdlJ10gPSRtZXNzYWdlOw0KICAgICAgICB9DQogICAgICAgIHJldHVybiByZXNwb25zZSgpLT5qc29uKCRyZXNwb25zZSwgJGNvZGUpOw0KICAgIH0NCiAgICBwdWJsaWMgZnVuY3Rpb24gc2VuZEVycm9yKCRlcnJvciwgJGVycm9yTWVzc2FnZXMgPSBbXSwgJGNvZGUgPSA0MDQpDQogICAgew0KICAgICAgICAkcmVzcG9uc2UgPSBbDQogICAgICAgICAgICAnc3VjY2VzcycgPT4gZmFsc2UsDQogICAgICAgICAgICAnbWVzc2FnZScgPT4gJGVycm9yLA0KICAgICAgICBdOw0KICANCiAgICAgICAgaWYoIWVtcHR5KCRlcnJvck1lc3NhZ2VzKSl7DQogICAgICAgICAgICAkcmVzcG9uc2VbJ2RhdGEnXSA9ICRlcnJvck1lc3NhZ2VzOw0KICAgICAgICB9DQogIA0KICAgICAgICByZXR1cm4gcmVzcG9uc2UoKS0+anNvbigkcmVzcG9uc2UsICRjb2RlKTsNCiAgICB9DQp9DQo=";
    createFile($responsePath . 'ApiResponseClass.php', base64_decode($responseCode));
}


// Update or create API route file
updateApiRoutes($input['tables']);

// Generate Migration
function generateMigration($table)
{
    global $migrationPath;

    // Get the current timestamp for migration file naming
    $timestamp = date('Y_m_d_His');
    $fileName = $migrationPath . $timestamp . '_create_' . strtolower($table['name']) . '_table.php';

    // Generate fields part of the migration
    $fields = generateMigrationFields($table['fields']);
    
    // Create the migration content
    $migrationContent = "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\n".
                        "return new class extends Migration {\n".
                        "    public function up(): void\n    {\n        Schema::create('". strtolower($table['name']) ."', function (Blueprint \$table) {\n".
                        "            \$table->id();\n".
                        "$fields".
                        "            \$table->timestamps();\n        });\n    }\n\n".
                        "    public function down(): void\n    {\n        Schema::dropIfExists('". strtolower($table['name']) ."');\n    }\n};\n";
    
    // Create the migration file
    createFile($fileName, $migrationContent, 'Migration');
}

// Generate Migration fields
function generateMigrationFields($fields)
{
    $output = '';
    foreach ($fields as $fieldName => $field) {
        $colDef = generateColumnDefinition($fieldName, $field);
        $output .= "            " . $colDef . "\n";
    }
    return $output;
}

// Generate Column Definition
function generateColumnDefinition($name, $column)
{
    $nullable = !isset($column['required']) || !$column['required'] ? '->nullable()' : '';
    
    // Map JSON types to Laravel migration types
    $typeMapping = [
        'string' => 'string',
        'integer' => 'integer',
        'date' => 'date',
        'bigint' => 'bigIncrements'
    ];

    $type = $typeMapping[$column['type']] ?? 'string'; // Default to 'string' if type is unknown

    // For bigIncrements and other fields that do not require a length
    if ($type === 'bigIncrements') {
        return "\$table->$type('$name');";
    } else {
        return "\$table->$type('$name')$nullable;";
    }
}

// Generate Controller
function generateController($table)
{
    global $controllerPath;

    $controllerName = ucfirst($table['name']) . 'Controller';
    
    // Generate the controller content
    $controllerContent = "<?php\n\nnamespace App\Http\Controllers;\n\nuse Illuminate\Http\Request;\nuse App\Models\\" . ucfirst($table['name']) . ";\nuse App\Http\Resources\\" . ucfirst($table['name']) . "Resource;\nuse App\Http\Requests\Store" . ucfirst($table['name']) . "Request;\nuse App\Http\Requests\Update" . ucfirst($table['name']) . "Request;\nuse App\Classes\ApiResponseClass;\n\nclass $controllerName extends Controller\n{\n" .
                         "    public function index()\n    {\n        return " . ucfirst($table['name']) . "::all();\n    }\n\n" .
                         "    public function store(Store" . ucfirst($table['name']) . "Request \$request)\n    {\n        \$data = " . ucfirst($table['name']) . "::create(\$request->validated());\n        return new " . ucfirst($table['name']) . "Resource(\$data);\n    }\n\n" .
                         "    public function show(" . ucfirst($table['name']) . " \$" . $table['name'] . ")\n    {\n        return new " . ucfirst($table['name']) . "Resource(\$" . $table['name'] . ");\n    }\n\n" .
                         "    public function update(Update" . ucfirst($table['name']) . "Request \$request, " . ucfirst($table['name']) . " \$" . $table['name'] . ")\n    {\n        \$" . $table['name'] . "->update(\$request->validated());\n        return new " . ucfirst($table['name']) . "Resource(\$" . $table['name'] . ");\n    }\n\n" .
                         "    public function destroy(" . ucfirst($table['name']) . " \$" . $table['name'] . ")\n    {\n        \$" . $table['name'] . "->delete();\n        return response()->json(['message' => 'Resource deleted successfully.']);\n    }\n}\n";

    // Create the controller file
    createFile($controllerPath . $controllerName . '.php', $controllerContent, 'Controller');
}


// Generate Request
function generateRequest($table, $type)
{
    global $requestPath;
    $fileName = $requestPath . $type . ucfirst($table['name']) . 'Request.php';
    $rules = generateValidationRules($table);
    
    $requestContent = "<?php\n\nnamespace App\Http\Requests;\n\nuse Illuminate\Foundation\Http\FormRequest;\n\nclass $type" . ucfirst($table['name']) . "Request extends FormRequest\n{\n" .
                    "    public function authorize(): bool\n    {\n        return true;\n    }\n\n" .
                    "    public function rules(): array\n    {\n        return [\n            $rules\n        ];\n    }\n}\n";
    
    createFile($fileName, $requestContent, 'Request');
}

// Generate Validation Rules for Requests
function generateValidationRules($tables)
{
    $rule = '';
        // Get the field names and properties
        foreach ($tables['fields'] as $fieldName => $fieldProperties) {
            // Start building the validation rule
            $rule = "'$fieldName' => '";
    
            // Add 'required' or 'nullable'
            if (isset($fieldProperties['required']) && $fieldProperties['required']) {
                $rule .= 'required|';
            } else {
                $rule .= 'nullable|';
            }
    
            // Add the type of field (string, integer, etc.)
            if (isset($fieldProperties['type'])) {
                $rule .= $fieldProperties['type'];
    
                // Add max length for strings as an example
                if ($fieldProperties['type'] == 'string') {
                    $rule .= '|max:255';
                }
            }
    
            // Close the validation rule string
            $rule .= "',";
        }
    return $rule;
  }

// Generate Resource
function generateResource($table)
{
    global $resourcePath;
    $fileName = $resourcePath . ucfirst($table['name']) . 'Resource.php';
    $resourceContent = "<?php\n\nnamespace App\Http\Resources;\n\nuse Illuminate\Http\Resources\Json\JsonResource;\n\nclass " . ucfirst($table['name']) . "Resource extends JsonResource\n{\n" .
                        "    public function toArray(\$request): array\n    {\n        return [\n            'id' => \$this->id,\n" .
                        generateResourceAttributes($table) .
                        "            'created_at' => \$this->created_at->toDateTimeString(),\n            'updated_at' => \$this->updated_at ? \$this->updated_at->toDateTimeString() : null,\n        ];\n    }\n}\n";
    
    createFile($fileName, $resourceContent, 'Resource');
}

// Generate Resource Attributes
function generateResourceAttributes($tables)
{
    $attributes = '';
    foreach ($tables['fields'] as $fields => $fieldProperties) {
        $attributes .= "\t\t\t'{$fields}' => \$this->{$fields},\n";
    }
    return $attributes;
}

// Check if file exists, and optionally overwrite

function createFile($fileName, $content,  $category = '')
{
	//global colors;
    static $overwriteAll = false;  // Static variable to keep track of the overwrite option
    static $skipAll = false;       // Static variable to skip all files

    // Check if the file already exists
    if (file_exists($fileName)) {
        // Only prompt the user if not set to overwrite or skip all
        if (!$overwriteAll && !$skipAll) {
            echo colors['green']."File exists:".colors['yellow']. basename($fileName). colors['green']." What do you want to do?".colors['reset'].EL;
            echo colors['dodger_blue3']."1. Overwrite this file".colors['reset'].EL;
            echo colors['dodger_blue3']."2. Skip this file".colors['reset'].EL;
            echo colors['dodger_blue3']."3. Overwrite all files".colors['reset'].EL;
            echo colors['dodger_blue3']."4. Skip all files".colors['reset'].EL;

            // Prompt user for their choice
            $handle = fopen("php://stdin", "r");
            $choice = trim(fgets($handle));

            // Handle user choice
            if ($choice == 1) {
                file_put_contents($fileName, $content);
                echo colors['green']."Overwritten file: $fileName".colors['reset'].EL;
            } elseif ($choice == 2) {
                echo colors['yellow']."Skipped file: $fileName".colors['reset'].EL;
                return; // Exit function to avoid overwriting
            } elseif ($choice == 3) {
                $overwriteAll = true;  // Set to overwrite all files
                file_put_contents($fileName, $content);
                echo colors['green']."Overwritten file: $fileName".colors['reset'].EL;
            } elseif ($choice == 4) {
                $skipAll = true;  // Set to skip all files
                echo colors['red']."Skipped file: $fileName\n".colors['reset'].EL;
                return; // Exit function to avoid overwriting
            } else {
                echo colors['red']."Invalid choice. Skipping file: $fileName\n".colors['reset'].EL;
                return; // Exit function for invalid input
            }
        } elseif ($overwriteAll) {
            // Overwrite file automatically if overwrite all is set
            file_put_contents($fileName, $content);
            echo colors['green']."Overwritten file: $fileName".colors['reset'].EL;
        } elseif ($skipAll) {
            // Skip file automatically if skip all is set
            echo colors['yellow']."Skipped file: $fileName".colors['reset'].EL;
            return;
        }
    } else {
        // If file does not exist, create it
    $targetLength = 100;
    file_put_contents($fileName, $content);
    $fileBasename = basename($fileName);
       // Form the message without the dots first
    $message = "\t| " . ucfirst($category) . " - " . $fileBasename;

    // Calculate the number of dots needed to align the Success message
    $dotsCount = $targetLength - strlen($message) - strlen(" Success");

    // Ensure at least one dot is printed if message length exceeds target length
    $dots = $dotsCount > 0 ? str_repeat('.', $dotsCount) : '.';

    // Output the final message with aligned dots and Success
    echo colors['yellow'] .$message. colors['light_sea_green'] . $dots . colors['green'] . " Success" . colors['reset'] . EL;
       // echo colors['yellow'].$fileBasename.colors['magenta']." Created".colors['reset'].EL;
    }
}

// Update API routes or print the new routes to console
function updateApiRoutes($tables)
{
    global $routePath;
    echo $routePath;
    $jsons = '{"api": {
        "use_middleware": true,
        "middleware": ["auth:sanctum"],
        "exclude_methods": ["index", "show"]
    }}';
    $config = json_decode($jsons, true);
    $apiConfig = isset($tables['api']) ? $tables['api'] : $config['api'];
    $newRoutes = '';
    $newRoutesConsole = '';
    $useStatements = '';
    $useStatementsConsole = '';
    
    // Terminal color codes for the console output
    $colors = colors;

    // Handle custom middleware from the config
    $middleware = $apiConfig['use_middleware'] ? implode(',', $apiConfig['middleware']) : '';
    
    // Get the excluded methods if available, otherwise include all methods
    $excludeMethods = isset($apiConfig['exclude_methods']) ? $apiConfig['exclude_methods'] : [];

    // Method definitions for CRUD
    $availableMethods = [
        'index' => "Route::get('/{name}',  'index');\n",
        'show' => "Route::get('/{name}/{id}',  'show');\n",
        'store' => "Route::post('/{name}',  'store');\n",
        'update' => "Route::put('/{name}/{id}',  'update');\n",
        'destroy' => "Route::delete('/{name}/{id}',  'destroy');\n"
    ];
    
    // Console-specific method definitions (with color codes)
    $availableMethodsConsole = [
        'index' => "{$colors['green']}Route{$colors['white']}::{$colors['yellow']}get{$colors['gold']}({$colors['dark_orange3']}'/{name}'{$colors['white']},{$colors['dark_orange3']} 'index'{$colors['gold']}){$colors['white']};{$colors['reset']}\n",
        'show' => "{$colors['green']}Route{$colors['white']}::{$colors['yellow']}get{$colors['gold']}({$colors['dark_orange3']}'/{name}/{id}'{$colors['white']}, {$colors['dark_orange3']}'show'{$colors['gold']}){$colors['white']};{$colors['reset']}\n",
        'store' => "{$colors['green']}Route{$colors['white']}::{$colors['yellow']}get{$colors['gold']}({$colors['dark_orange3']}'/{name}'{$colors['white']}, {$colors['dark_orange3']} 'store'{$colors['gold']}){$colors['white']};{$colors['reset']}\n",
        'update' => "{$colors['green']}Route{$colors['white']}::{$colors['yellow']}get{$colors['gold']}({$colors['dark_orange3']}'/{name}/{id}'{$colors['white']}, {$colors['dark_orange3']} 'update'{$colors['gold']}){$colors['white']};{$colors['reset']}\n",
        'destroy' => "{$colors['green']}Route{$colors['white']}::{$colors['yellow']}get{$colors['gold']}({$colors['dark_orange3']}'/{name}/{id}'{$colors['white']}, {$colors['dark_orange3']} 'destroy'{$colors['gold']}){$colors['white']};{$colors['reset']}\n",
    ];

    foreach ($tables as $table) {
        $controller = ucfirst($table['name']) . 'Controller';
        $useStatements .= "use App\Http\Controllers\\$controller;\n";
        $useStatementsConsole .= "{$colors['cyan']}use {$colors['white']}App\Http\Controllers\\{$colors['green']}$controller;{$colors['reset']}\n";

        // Non-middleware routes without middleware
        $nonMiddlewareRoutes = '';
        $nonMiddlewareRoutesConsole = '';
        foreach ($availableMethods as $method => $routeDefinition) {
            if (in_array($method, $excludeMethods)) {
                $nonMiddlewareRoutes .= "    " . str_replace('{name}', $table['name'], $routeDefinition);
            }
        }
        foreach ($availableMethodsConsole as $method => $routeDefinition) {
            if (in_array($method, $excludeMethods)) {
                $nonMiddlewareRoutesConsole .= "    " . str_replace('{name}', $table['name'], $routeDefinition);
            }
        }
        if ($nonMiddlewareRoutes) {
            $newRoutes .= "Route::controller($controller::class)->group(function () {\n" . $nonMiddlewareRoutes . "});\n\n";
        }
        if ($nonMiddlewareRoutesConsole) {
            $newRoutesConsole .= "{$colors['green']}Route{$colors['white']}::{$colors['yellow']}controller{$colors['dodger_blue3']}({$colors['light_sea_green']}{$controller}{$colors['white']}::{$colors['cyan']}class{$colors['dodger_blue3']}){$colors['yellow']}->group{$colors['gold']}({$colors['cyan']}function {$colors['magenta3']}() {{$colors['reset']}\n" 
                . $nonMiddlewareRoutesConsole . "{$colors['magenta3']}}{$colors['gold']});{$colors['reset']}\n\n";
        }

        // Middleware routes
        $middlewareRoutes = '';
        $middlewareRoutesConsole = '';
        foreach ($availableMethods as $method => $routeDefinition) {
            if (!in_array($method, $excludeMethods)) {
                $middlewareRoutes .= "    " . str_replace('{name}', $table['name'], $routeDefinition);
            }
        }
        foreach ($availableMethodsConsole as $method => $routeDefinition) {
            if (!in_array($method, $excludeMethods)) {
                $middlewareRoutesConsole .= "    " . str_replace('{name}', $table['name'], $routeDefinition);
            }
        }
        if ($middlewareRoutes) {
            $newRoutes .= "Route::controller($controller::class)->group(function () {\n" . $middlewareRoutes . "})->middleware('$middleware');\n\n";
        }
        if ($middlewareRoutesConsole) {
            $newRoutesConsole .= "{$colors['green']}Route{$colors['white']}::{$colors['yellow']}controller{$colors['dodger_blue3']}({$colors['light_sea_green']}{$controller}{$colors['white']}::{$colors['cyan']}class{$colors['dodger_blue3']}){$colors['yellow']}->group{$colors['gold']}({$colors['cyan']}function {$colors['magenta3']}() {\n" 
                . $middlewareRoutesConsole . "{$colors['magenta3']}}{$colors['gold']}){$colors['yellow']}->middleware{$colors['gold']}({$colors['dark_orange3']}'$middleware'{$colors['gold']}){$colors['white']};{$colors['reset']}\n\n";
        }
    }

    // Write to api.php file
    if (file_exists($routePath)) {
        // Append new use statements and routes (without color)
        //file_put_contents($routePath, $useStatements . "\n" . $newRoutes, FILE_APPEND);
      // Read the file content
    $fileContent = file_get_contents($routePath);

    // Split the file content by lines
    $fileLines = explode("\n", $fileContent);

    // Find the last `use` statement in the file
    $lastUseStatementIndex = -1;
    foreach ($fileLines as $index => $line) {
        if (preg_match('/^use\s+/', $line)) {
            $lastUseStatementIndex = $index;
        }
    }

    // If use statements exist, insert $useStatements after the last one
    if ($lastUseStatementIndex >= 0) {
        array_splice($fileLines, $lastUseStatementIndex + 1, 0, $useStatements);
    } else {
        // If no use statements, place $useStatements at the top after the <?php tag
        array_splice($fileLines, 1, 0, $useStatements);
    }

    // Append $newRoutes at the end of the file
    array_push($fileLines, "\n" . $newRoutes);

    // Join the updated lines back into a string
    $updatedContent = implode("\n", $fileLines);

    // Write the updated content to the file
    file_put_contents($routePath, $updatedContent);
        echo "API routes and controllers updated in api.php".EL;
    } else {
        // Output to console (with colors)
        echo "API routes file not found. Add the following manually:".EL.EL;
        echo $useStatementsConsole . "\n" . $newRoutesConsole;
    }
}


// Function to generate Model
function generateModel($table)
{
    
	$fields = $table['fields'];
    $modelName = $table['name'];
    $fillable='';
	foreach ($table['fields'] as $fields => $fieldProperties) {
		$fillable .= "'{$fields}',\n";
	}

    
    $modelContent = "<?php\n\nnamespace App\Models;\n\nuse Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;\n\nclass $modelName extends Model\n{\n    use HasFactory;\n\n    protected \$fillable = [" .  $fillable . "];\n}\n";
    
    createFile(__DIR__ . "/app/Models/".$table['name'].".php", $modelContent, 'Model');
}

?>