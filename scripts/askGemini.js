// scripts/askGemini.js
// Change 'require' to 'import'
import { GoogleGenerativeAI } from '@google/generative-ai';

// Get API key from environment variable
// For local development, process.env will read from the system environment.
// When called from Laravel, you'll pass it as an argument.
const API_KEY = process.argv[2]; // The 3rd argument passed to the script

async function runGemini() {
  if (!API_KEY) {
    console.error("Error: GEMINI_API_KEY is not provided.");
    process.exit(1); // Exit with an error code
  }

  const genAI = new GoogleGenerativeAI(API_KEY);
  const model = genAI.getGenerativeModel({ model: "gemini-pro" }); // Or "gemini-1.5-flash", etc.

  // The user's prompt will be the 4th argument passed to the script
  const prompt = process.argv[3];

  if (!prompt) {
    console.error("Error: No prompt provided.");
    process.exit(1); // Exit with an error code
  }

  try {
    const result = await model.generateContent(prompt);
    const response = await result.response;
    console.log(response.text()); // Print the response to standard output
  } catch (error) {
    console.error("Error calling Gemini:", error.message);
    process.exit(1); // Exit with an error code on API error
  }
}

runGemini();