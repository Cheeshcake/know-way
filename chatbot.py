#!/usr/bin/env python3
# chatbot.py - KnowWay's AI assistant using OpenAI API

import sys
import os
import json
from datetime import datetime

# Try to import OpenAI library, with fallback for different versions
try:
    from openai import OpenAI
    USING_NEW_API = True
except ImportError:
    try:
        import openai
        USING_NEW_API = False
    except ImportError:
        print("OpenAI library not found. Please install it using: pip install openai")
        sys.exit(1)

# Get API key from environment variable or use the provided one
# IMPORTANT: In production, always use environment variables for API keys
API_KEY = os.environ.get("OPENAI_API_KEY", "your_openai_api_key")  # Replace with your actual key

# Define system prompt for the assistant
SYSTEM_PROMPT = """
You are KnowWay's helpful learning assistant. KnowWay is an online learning platform offering courses in various subjects.
- Be friendly, concise, and helpful
- Focus on education and learning-related questions
- Recommend courses based on user interests
- Provide learning tips and resources
- Keep responses under 150 words
- If you don't know something, admit it and offer to help with something else
"""

def get_response(question):
    """Get a response from the OpenAI API"""
    try:
        if USING_NEW_API:
            # New OpenAI API (v1.0.0+)
            client = OpenAI(api_key=API_KEY)
            response = client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {"role": "system", "content": SYSTEM_PROMPT},
                    {"role": "user", "content": question}
                ],
                max_tokens=300
            )
            return response.choices[0].message.content.strip()
        else:
            # Legacy OpenAI API
            openai.api_key = API_KEY
            response = openai.ChatCompletion.create(
                model="gpt-3.5-turbo",
                messages=[
                    {"role": "system", "content": SYSTEM_PROMPT},
                    {"role": "user", "content": question}
                ],
                max_tokens=300
            )
            return response.choices[0].message['content'].strip()
    except Exception as e:
        # Log the error
        with open("chatbot_errors.log", "a") as f:
            f.write(f"{datetime.now()} - Error: {str(e)}\n")
        return "I'm having trouble connecting to my knowledge base right now. Please try again later."

if __name__ == "__main__":
    # Get the question from command line arguments
    if len(sys.argv) > 1:
        question = " ".join(sys.argv[1:])
        response = get_response(question)
        print(response)
    else:
        print("Please provide a question.")
