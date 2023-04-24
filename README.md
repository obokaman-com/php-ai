# PhpAi

This is a demo application to show how to implement OpenAI's GPT API into a PHP Symfony application, training the AI with some documents via embeddings to give it some custom knowledge and context, and long-term memory.

This is a work-in-progress.

To start working, 

1. **Clone this repo locally:**
```bash
git clone git@github.com:obokaman-com/php-ai.git
```

2. **Copy `.env` into `.env.local` and complete with your OpenAI API Key.**
```bash
cd php-ai && cp .env .env.local
```

3. **Install all dependencies**
```bash
composer install
```

4. **Put any required documents in `/public/docs_to_ingest` folder (now only works with PDFs)**
5. **Ingest them into memory:**
```bash
bin/console memory:ingest
```

6. **Ask a question:**
```bash
bin/console ai:question
```
For interactive question, or
```bash
bin/console ai:question --question "My question"
```
For direct questioning the AI.
