<?php

// A basic MCP client example

require 'vendor/autoload.php';

use Mcp\Client\Client;
use Mcp\Client\Transport\StdioServerParameters;
use Mcp\Types\TextContent;

// Create server parameters for stdio connection
$serverParams = new StdioServerParameters(
    command: 'php',  // Executable
    args: ['my_mcp_server.php'],  // File path to the server
    env: null  // Optional environment variables
);

echo "=== MCP Client Demo ===\n\n";

// Create client instance
$client = new Client();

try {
    echo "Connecting to MCP server...\n";
    // Connect to the server using stdio transport
    $session = $client->connect(
        commandOrUrl: $serverParams->getCommand(),
        args: $serverParams->getArgs(),
        env: $serverParams->getEnv()
    );

    echo "Connected successfully!\n\n";

    // 1. List available prompts
    echo "=== Available Prompts ===\n";
    $promptsResult = $session->listPrompts();

    if (!empty($promptsResult->prompts)) {
        foreach ($promptsResult->prompts as $prompt) {
            echo "📝 Prompt: " . $prompt->name . "\n";
            echo "   Description: " . $prompt->description . "\n";
            echo "   Arguments:\n";
            if (!empty($prompt->arguments)) {
                foreach ($prompt->arguments as $argument) {
                    $required = $argument->required ? "required" : "optional";
                    echo "     - {$argument->name} ({$required}): {$argument->description}\n";
                }
            } else {
                echo "     (None)\n";
            }
            echo "\n";
        }
    } else {
        echo "No prompts available.\n\n";
    }

    // 2. Test a prompt
    echo "=== Testing Greeting Prompt ===\n";
    try {
        $greetingResult = $session->getPrompt('greeting', ['name' => 'Alice', 'language' => 'es']);
        if (!empty($greetingResult->messages)) {
            foreach ($greetingResult->messages as $message) {
                if ($message->content instanceof TextContent) {
                    echo "Greeting: " . $message->content->text . "\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "Error getting greeting: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 3. List available tools
    echo "=== Available Tools ===\n";
    $toolsResult = $session->listTools();

    if (!empty($toolsResult->tools)) {
        foreach ($toolsResult->tools as $tool) {
            echo "🔧 Tool: " . $tool->name . "\n";
            echo "   Description: " . $tool->description . "\n";
            echo "\n";
        }
    } else {
        echo "No tools available.\n\n";
    }

    // 4. Test calculator tool
    echo "=== Testing Calculator Tool ===\n";
    try {
        $calcResult = $session->callTool('calculator', [
            'operation' => 'add',
            'a' => 15,
            'b' => 27
        ]);
        
        if (!empty($calcResult->content)) {
            foreach ($calcResult->content as $content) {
                if ($content instanceof TextContent) {
                    echo "Calculator result: " . $content->text . "\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "Error using calculator: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 5. Test datetime tool
    echo "=== Testing DateTime Tool ===\n";
    try {
        $datetimeResult = $session->callTool('datetime', [
            'format' => 'Y-m-d H:i:s',
            'timezone' => 'America/New_York'
        ]);
        
        if (!empty($datetimeResult->content)) {
            foreach ($datetimeResult->content as $content) {
                if ($content instanceof TextContent) {
                    echo "DateTime result: " . $content->text . "\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "Error getting datetime: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 6. Test web search tool
    echo "=== Testing Web Search Tool ===\n";
    try {
        $searchResult = $session->callTool('web_search', [
            'query' => 'Charles Babbage computer history',
            'max_results' => 3
        ]);
        
        if (!empty($searchResult->content)) {
            foreach ($searchResult->content as $content) {
                if ($content instanceof TextContent) {
                    echo $content->text . "\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "Error performing web search: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 7. Test story prompt
    echo "=== Testing Story Prompt ===\n";
    try {
        $storyResult = $session->getPrompt('story', ['theme' => 'space adventure', 'length' => 'medium']);
        if (!empty($storyResult->messages)) {
            foreach ($storyResult->messages as $message) {
                if ($message->content instanceof TextContent) {
                    echo "Story: " . $message->content->text . "\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "Error getting story: " . $e->getMessage() . "\n";
    }

    echo "\n=== Demo Complete ===\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // Close the server connection
    if (isset($client)) {
        $client->close();
    }
}
