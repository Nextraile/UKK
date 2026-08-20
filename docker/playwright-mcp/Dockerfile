FROM node:24-slim

ENV PLAYWRIGHT_BROWSERS_PATH=/root/.cache/ms-playwright

WORKDIR /app

RUN apt-get update -qq \
    && apt-get install -y --no-install-recommends \
        libnss3 libnspr4 libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 \
        libxkbcommon0 libxcomposite1 libxdamage1 libxfixes3 libxrandr2 libgbm1 \
        libasound2 libatspi2.0-0 libpango-1.0-0 libcairo2 libdbus-1-3 libexpat1 \
        libglib2.0-0 libx11-6 libxcb1 libxext6 libfontconfig1 \
    && rm -rf /var/lib/apt/lists/*

RUN npm install -g @playwright/mcp@latest playwright@1.63.0-alpha-2026-08-05 \
    && playwright install chrome chromium

EXPOSE 8931

CMD ["playwright-mcp", "--headless", "--no-sandbox", "--host", "0.0.0.0", "--browser", "chromium", "--port", "8931"]