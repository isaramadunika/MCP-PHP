<?php

// A basic MCP server example

require 'vendor/autoload.php';

use Mcp\Server\Server;
use Mcp\Server\ServerRunner;
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
 * In a real implementation, you would integrate with Tavily API or another search service
 */
function performWebSearch(string $query, int $maxResults = 5): string {
    // For demo purposes, we'll simulate search results
    // In a real implementation, you would make HTTP requests to Tavily API
    
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

// Create a server instance
$server = new Server('my-mcp-server');

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
            'en' => "Hello $personName! Welcome to our MCP server.",
            'es' => "¡Hola $personName! Bienvenido a nuestro servidor MCP.",
            'fr' => "Bonjour $personName! Bienvenue sur notre serveur MCP."
        ];
        
        $greeting = $greetings[$language] ?? $greetings['en'];
        
        return new GetPromptResult(
            messages: [
                new PromptMessage(
                    role: Role::USER,
                    content: new TextContent(text: $greeting)
                )
            ],
            description: 'Personalized greeting'
        );
    }
    
    if ($name === 'story') {
        $theme = $arguments->theme ?? 'adventure';
        $length = $arguments->length ?? 'short';
        
        $story = "Once upon a time, there was a great $theme. ";
        if ($length === 'medium') {
            $story .= "The characters faced many challenges and discovered hidden truths. ";
        } elseif ($length === 'long') {
            $story .= "The characters faced many challenges, discovered hidden truths, and formed lasting friendships through their journey. They learned valuable lessons about courage, friendship, and perseverance. ";
        }
        $story .= "And they all lived happily ever after.";
        
        return new GetPromptResult(
            messages: [
                new PromptMessage(
                    role: Role::USER,
                    content: new TextContent(text: $story)
                )
            ],
            description: 'Generated story'
        );
    }

    throw new \InvalidArgumentException("Unknown prompt: {$name}");
});

// Register tool handlers
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
            description: 'Get current date and time information',
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
            description: 'Search the web using Tavily search API',
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
            content: [new TextContent(text: "Result: $result")],
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
                content: [new TextContent(text: "Current date/time: $result (Timezone: $timezone)")],
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
            // Simulate a web search result (you can integrate with actual Tavily API here)
            $searchResults = performWebSearch($query, $maxResults);
            
            return new CallToolResult(
                content: [new TextContent(text: $searchResults)],
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

// Create initialization options and run server
$initOptions = $server->createInitializationOptions();
$runner = new ServerRunner($server, $initOptions);
$runner->run();
