<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GeminiController extends Controller
{
    public function generateText(Request $request)
    {
        // Get the prompt from the request (e.g., from a form input)
        $userPrompt = $request->input('prompt', 'Tell me a fun fact about Indonesia.');

        // Get the API Key from your .env file
        $geminiApiKey = env('GEMINI_API_KEY');

        if (empty($geminiApiKey)) {
            return response()->json(['error' => 'Gemini API Key is not configured.'], 500);
        }

        // Define the path to your Node.js script
        $scriptPath = base_path('scripts/askGemini.js'); // Adjust path if you put it elsewhere

        // Create the process command: node [script path] [api key] "[your prompt]"
        // We pass the API Key and prompt as arguments to the Node.js script
        $process = new Process(['node', $scriptPath, $geminiApiKey, $userPrompt]);

        try {
            $process->run();

            // Check if the command was successful
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Get the output from the Node.js script
            $geminiResponse = trim($process->getOutput());

            return response()->json(['success' => true, 'gemini_response' => $geminiResponse]);

        } catch (ProcessFailedException $exception) {
            // Log or handle the error appropriately
            \Log::error('Gemini Script Error: ' . $exception->getMessage());
            return response()->json(['error' => 'Failed to get response from Gemini.', 'details' => $exception->getMessage()], 500);
        }
    }
}
