FROM node:20-alpine

WORKDIR /usr/src/app

EXPOSE 3000

CMD ["sh", "-lc", "npm install && npx nuxt dev --hostname 0.0.0.0 --port 3000"]

# Production steps (reference)
# COPY package*.json ./
# RUN npm ci
# COPY . .
# RUN npm run build
# CMD ["npm", "run", "start"]
