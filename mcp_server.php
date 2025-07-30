<?php

// Simplified MCP Server for Railway hosting

require_once __DIR__ . '/vendor/autoload.php';

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
use Mcp\Types\ToolInputProperties;

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
            'title' => "Images related to '$query'",
            'url' => 'https://images.google.com/search?q=' . urlencode($query),
            'snippet' => "Visual content and infographics that illustrate concepts related to $query."
        ]
    ];
    
    // Limit results and format output
    $limitedResults = array_slice($sampleResults, 0, min($maxResults, count($sampleResults)));
    
    $output = "Search Results for: '$query'\n\n";
    foreach ($limitedResults as $index => $result) {
        $num = $index + 1;
        $output .= "{$num}. {$result['title']}\n";
        $output .= "   URL: {$result['url']}\n";
        $output .= "   {$result['snippet']}\n\n";
    }
    
    return $output;
}

try {
    // Create the MCP server
    $server = new Server("mcp-php-railway");

    // Register tools list handler
    $server->registerHandler('tools/list', function($params) {
        $tools = [
            new Tool(
                name: 'calculator',
                description: 'Perform basic arithmetic operations',
                inputSchema: ToolInputSchema::fromArray([
                    'type' => 'object',
                    'properties' => [
                        'operation' => [
                            'type' => 'string',
                            'enum' => ['add', 'subtract', 'multiply', 'divide'],
                            'description' => 'The operation to perform'
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
                description: 'Get current date and time',
                inputSchema: ToolInputSchema::fromArray([
                    'type' => 'object',
                    'properties' => [
                        'timezone' => [
                            'type' => 'string',
                            'description' => 'Timezone (default: UTC)',
                            'default' => 'UTC'
                        ],
                        'format' => [
                            'type' => 'string',
                            'description' => 'Date format (default: Y-m-d H:i:s)',
                            'default' => 'Y-m-d H:i:s'
                        ]
                    ]
                ])
            ),
            new Tool(
                name: 'web_search',
                description: 'Search the web (simulated)',
                inputSchema: ToolInputSchema::fromArray([
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'Search query'
                        ],
                        'max_results' => [
                            'type' => 'integer',
                            'description' => 'Maximum results (default: 5)',
                            'default' => 5
                        ]
                    ],
                    'required' => ['query']
                ])
            )
        ];
        
        return new ListToolsResult($tools);
    });

    // Register tool call handler
    $server->registerHandler('tools/call', function(CallToolRequestParams $params): CallToolResult {
        $toolName = $params->name;
        $args = $params->arguments;

        switch ($toolName) {
            case "calculator":
                $operation = $args->operation ?? '';
                $a = floatval($args->a ?? 0);
                $b = floatval($args->b ?? 0);

                $result = match ($operation) {
                    "add" => $a + $b,
                    "subtract" => $a - $b,
                    "multiply" => $a * $b,
                    "divide" => $b != 0 ? $a / $b : "Error: Division by zero",
                    default => "Error: Unknown operation"
                };

                return new CallToolResult(
                    content: [new TextContent(text: "[RAILWAY] Calculator: $a $operation $b = $result")],
                    isError: false
                );

            case "datetime":
                $timezone = $args->timezone ?? 'UTC';
                $format = $args->format ?? 'Y-m-d H:i:s';

                try {
                    $date = new DateTime('now', new DateTimeZone($timezone));
                    $result = $date->format($format);
                    
                    return new CallToolResult(
                        content: [new TextContent(text: "[RAILWAY] Current date/time: $result (Timezone: $timezone)")],
                        isError: false
                    );
                } catch (Exception $e) {
                    return new CallToolResult(
                        content: [new TextContent(text: "Error: " . $e->getMessage())],
                        isError: true
                    );
                }

            case "web_search":
                $query = $args->query ?? '';
                $maxResults = intval($args->max_results ?? 5);

                if (empty($query)) {
                    return new CallToolResult(
                        content: [new TextContent(text: "Error: Search query is required")],
                        isError: true
                    );
                }

                try {
                    $searchResults = performWebSearch($query, $maxResults);
                    
                    return new CallToolResult(
                        content: [new TextContent(text: "[RAILWAY] " . $searchResults)],
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

    // Configure session store
    $sessionDir = sys_get_temp_dir() . '/mcp_sessions';
    if (!is_dir($sessionDir)) {
        mkdir($sessionDir, 0755, true);
    }

    // HTTP configuration for Railway
    $httpOptions = [
        'session_timeout' => 300,
        'max_queue_size' => 50,
        'enable_sse' => false,
        'shared_hosting' => false,
        'server_header' => 'MCP-PHP-Railway/1.0',
    ];

    // Create and run the server
    $fileStore = new FileSessionStore($sessionDir);
    $runner = new HttpServerRunner($server, $server->createInitializationOptions(), $httpOptions, null, $fileStore);
    $adapter = new StandardPhpAdapter($runner);

    // Set CORS headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Handle the request
    $adapter->handle();

} catch (\Exception $e) {
    error_log("MCP Server Error: " . $e->getMessage());
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => 'MCP Server Error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}
