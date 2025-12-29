FROM node:20-alpine

RUN apk add --no-cache \
    g++ \
    make \
    python3 \
    vips-dev

ENV npm_config_build_from_source=true

WORKDIR /usr/src/app

EXPOSE 3000

CMD ["sh", "-lc", "npm install && NODE_OPTIONS=--max-old-space-size=4096 npx nuxt dev --hostname 0.0.0.0 --port 3000"]

# Production steps (reference)
# COPY package*.json ./
# RUN npm ci
# COPY . .
# RUN npm run build
# CMD ["npm", "run", "start"]
