<?php

// HTTP MCP Server for Railway deployment

// Don't send headers immediately - let the MCP server handle them
// Only handle OPTIONS requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit;
}

require 'vendor/autoload.php';

use Mcp\Server\Server;
use Mcp\Server\HttpServerRunner;
use Mcp\Server\Transport\Http\StandardPhpAdapter;
use Mcp\Server\Transport\Http\FileSessionStore;
use Mcp\Types\Prompt;
use Mcp\Types\PromptArgument;
use Mcp\Types\PromptMessage;
use Mcp\Types\ListPromptsResult;
use Mcp\Types\TextContent;
use Mcp\Types\Role;
use Mcp\Types\GetPromptResult;
use Mcp\Types\GetPromptRequestParams;
use Mcp\Types\Tool;
use Mcp\Types\ListToolsResult;
use Mcp\Types\CallToolResult;
use Mcp\Types\CallToolRequestParams;
use Mcp\Types\ToolInputSchema;

/**
 * Perform web search using a simple simulation
 */
function performWebSearch(string $query, int $maxResults = 5): string {
    $sampleResults = [
        [
            'title' => "Search result for '$query' - Wikipedia",
            'url' => 'https://en.wikipedia.org/wiki/' . urlencode($query),
            'snippet' => "Wikipedia article about $query with comprehensive information and references."
        ],
        [
            'title' => "Latest news about '$query'",
            'url' => 'https://news.example.com/search?q=' . urlencode($query),
            'snippet' => "Recent news articles and updates related to $query from various sources."
        ],
        [
            'title' => "Academic research on '$query'",
            'url' => 'https://scholar.google.com/scholar?q=' . urlencode($query),
            'snippet' => "Scholarly articles and research papers about $query from academic institutions."
        ],
        [
            'title' => "Video content about '$query'",
            'url' => 'https://youtube.com/results?search_query=' . urlencode($query),
            'snippet' => "Educational and informational videos explaining various aspects of $query."
        ],
        [
            'title' => "Official documentation for '$query'",
            'url' => 'https://docs.example.com/' . urlencode($query),
            'snippet' => "Official documentation and guides related to $query with technical details."
        ]
    ];
    
    $results = array_slice($sampleResults, 0, min($maxResults, count($sampleResults)));
    
    $output = "🔍 Web Search Results for: '$query'\n\n";
    
    foreach ($results as $index => $result) {
        $output .= ($index + 1) . ". **{$result['title']}**\n";
        $output .= "   URL: {$result['url']}\n";
        $output .= "   📄 {$result['snippet']}\n\n";
    }
    
    $output .= "💡 Note: These are simulated results. In a production environment, this would connect to the actual Tavily search API.";
    
    return $output;
}

try {
    // Create a server instance
    $server = new Server('hosted-mcp-php-server');

    // Register prompt handlers
    $server->registerHandler('prompts/list', function($params) {
        $prompts = [
            new Prompt(
                name: 'greeting',
                description: 'Generate a personalized greeting',
                arguments: [
                    new PromptArgument(
                        name: 'name',
                        description: 'The name of the person to greet',
                        required: true
                    ),
                    new PromptArgument(
                        name: 'language',
                        description: 'Language for the greeting (en, es, fr)',
                        required: false
                    )
                ]
            ),
            new Prompt(
                name: 'story',
                description: 'Generate a short story',
                arguments: [
                    new PromptArgument(
                        name: 'theme',
                        description: 'Theme of the story',
                        required: true
                    ),
                    new PromptArgument(
                        name: 'length',
                        description: 'Length (short, medium, long)',
                        required: false
                    )
                ]
            )
        ];
        
        return new ListPromptsResult($prompts);
    });

    $server->registerHandler('prompts/get', function(GetPromptRequestParams $params) {
        $name = $params->name;
        $arguments = $params->arguments;

        if ($name === 'greeting') {
            $personName = $arguments->name ?? 'Friend';
            $language = $arguments->language ?? 'en';
            
            $greetings = [
                'en' => "Hello $personName! Welcome to our hosted MCP server on Railway.",
                'es' => "¡Hola $personName! Bienvenido a nuestro servidor MCP alojado en Railway.",
                'fr' => "Bonjour $personName! Bienvenue sur notre serveur MCP hébergé sur Railway."
            ];
            
            $greeting = $greetings[$language] ?? $greetings['en'];
            
            return new GetPromptResult(
                messages: [
                    new PromptMessage(
                        role: Role::USER,
                        content: new TextContent(text: $greeting)
                    )
                ],
                description: 'Personalized greeting from hosted server'
            );
        }
        
        if ($name === 'story') {
            $theme = $arguments->theme ?? 'adventure';
            $length = $arguments->length ?? 'short';
            
            $story = "Once upon a time in the cloud, there was a great $theme. ";
            if ($length === 'medium') {
                $story .= "The hosted characters faced distributed challenges and discovered serverless truths. ";
            } elseif ($length === 'long') {
                $story .= "The hosted characters faced distributed challenges, discovered serverless truths, and formed scalable friendships through their cloud journey. They learned valuable lessons about resilience, automation, and digital transformation. ";
            }
            $story .= "And they all lived happily ever after in the cloud.";
            
            return new GetPromptResult(
                messages: [
                    new PromptMessage(
                        role: Role::USER,
                        content: new TextContent(text: $story)
                    )
                ],
                description: 'Cloud-themed story from hosted server'
            );
        }

        throw new \InvalidArgumentException("Unknown prompt: {$name}");
    });

    // Register tool handlers
    $server->registerHandler('tools/list', function($params) {
        $tools = [
            new Tool(
                name: 'calculator',
                description: 'Perform basic arithmetic operations (hosted version)',
                inputSchema: ToolInputSchema::fromArray([
                    'type' => 'object',
                    'properties' => [
                        'operation' => [
                            'type' => 'string',
                            'description' => 'The operation to perform (add, subtract, multiply, divide)',
                            'enum' => ['add', 'subtract', 'multiply', 'divide']
                        ],
                        'a' => [
                            'type' => 'number',
                            'description' => 'First number'
                        ],
                        'b' => [
                            'type' => 'number',
                            'description' => 'Second number'
                        ]
                    ],
                    'required' => ['operation', 'a', 'b']
                ])
            ),
            new Tool(
                name: 'datetime',
                description: 'Get current date and time information (hosted version)',
                inputSchema: ToolInputSchema::fromArray([
                    'type' => 'object',
                    'properties' => [
                        'format' => [
                            'type' => 'string',
                            'description' => 'Date format (default: Y-m-d H:i:s)',
                            'default' => 'Y-m-d H:i:s'
                        ],
                        'timezone' => [
                            'type' => 'string',
                            'description' => 'Timezone (default: UTC)',
                            'default' => 'UTC'
                        ]
                    ]
                ])
            ),
            new Tool(
                name: 'web_search',
                description: 'Search the web using hosted search service',
                inputSchema: ToolInputSchema::fromArray([
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'The search query to look up on the web'
                        ],
                        'max_results' => [
                            'type' => 'integer',
                            'description' => 'Maximum number of results to return (default: 5)',
                            'default' => 5
                        ]
                    ],
                    'required' => ['query']
                ])
            )
        ];
        
        return new ListToolsResult($tools);
    });

    $server->registerHandler('tools/call', function(CallToolRequestParams $params) {
        $toolName = $params->name;
        $arguments = $params->arguments;

        if ($toolName === 'calculator') {
            $operation = $arguments['operation'];
            $a = floatval($arguments['a']);
            $b = floatval($arguments['b']);
            
            $result = match($operation) {
                'add' => $a + $b,
                'subtract' => $a - $b,
                'multiply' => $a * $b,
                'divide' => $b != 0 ? $a / $b : 'Error: Division by zero',
                default => 'Error: Unknown operation'
            };
            
            return new CallToolResult(
                content: [new TextContent(text: "Hosted Calculator Result: $result")],
                isError: false
            );
        }
        
        if ($toolName === 'datetime') {
            $format = $arguments['format'] ?? 'Y-m-d H:i:s';
            $timezone = $arguments['timezone'] ?? 'UTC';
            
            try {
                $dateTime = new DateTime('now', new DateTimeZone($timezone));
                $result = $dateTime->format($format);
                
                return new CallToolResult(
                    content: [new TextContent(text: "Hosted Server Time: $result (Timezone: $timezone)")],
                    isError: false
                );
            } catch (Exception $e) {
                return new CallToolResult(
                    content: [new TextContent(text: "Error: " . $e->getMessage())],
                    isError: true
                );
            }
        }
        
        if ($toolName === 'web_search') {
            $query = $arguments['query'] ?? '';
            $maxResults = $arguments['max_results'] ?? 5;
            
            if (empty($query)) {
                return new CallToolResult(
                    content: [new TextContent(text: "Error: Search query cannot be empty")],
                    isError: true
                );
            }
            
            try {
                $searchResults = performWebSearch($query, $maxResults);
                
                return new CallToolResult(
                    content: [new TextContent(text: "[HOSTED] " . $searchResults)],
                    isError: false
                );
            } catch (Exception $e) {
                return new CallToolResult(
                    content: [new TextContent(text: "Error performing search: " . $e->getMessage())],
                    isError: true
                );
            }
        }

        throw new \InvalidArgumentException("Unknown tool: {$toolName}");
    });

    // Configure HTTP options for Railway
    $httpOptions = [
        'session_timeout' => 300,    // 5 minutes for faster cleanup
        'max_queue_size' => 100,     // Smaller queue for Railway
        'enable_sse' => false,       // Disable SSE for Railway compatibility
        'shared_hosting' => false,   // Railway is not shared hosting
        'server_header' => 'MCP-PHP-Railway/1.0',
    ];

    // Create session store directory if it doesn't exist
    $sessionDir = __DIR__ . '/mcp_sessions';
    if (!is_dir($sessionDir)) {
        mkdir($sessionDir, 0755, true);
    }

    // Create the adapter and handle the request
    try {
        // 1) Create a file-based store
        $fileStore = new FileSessionStore($sessionDir); 
        
        // 2) Create a runner that uses that store
        $runner = new HttpServerRunner($server, $server->createInitializationOptions(), $httpOptions, null, $fileStore);
        
        // 3) Create a StandardPhpAdapter and pass your runner in directly
        $adapter = new StandardPhpAdapter($runner);
        
        // 4) Set CORS headers before handling
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // 5) Handle the request
        $adapter->handle();
        
    } catch (\Exception $e) {
        // Log error but don't expose details in production
        error_log("MCP Server Error: " . $e->getMessage());
        
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal Server Error',
            'message' => 'MCP server encountered an error',
            'timestamp' => date('c')
        ]);
    }

} catch (\Exception $e) {
    // Outer exception handler for server setup errors
    error_log("MCP Server Setup Error: " . $e->getMessage());
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => 'Server Setup Error',
        'message' => 'Failed to initialize MCP server',
        'timestamp' => date('c')
    ]);
}
