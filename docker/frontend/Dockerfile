# Frontend Dockerfile (multi-stage)
# Build stage
FROM node:18-alpine AS build

WORKDIR /app
ENV NODE_ENV=production

# Install dependencies (uses package-lock.json if present)
COPY package*.json ./
COPY package-lock.json ./
RUN npm ci --silent --no-audit --progress=false || npm install --silent

# Copy source and build
COPY . .
RUN npm run build || npm run build --if-present

# Production image
FROM nginx:stable-alpine AS runtime

# Remove default nginx static files
RUN rm -rf /usr/share/nginx/html/*

# Copy built assets
COPY --from=build /app/dist /usr/share/nginx/html

# Expose port
EXPOSE 80

# Use default nginx foreground command
CMD ["nginx", "-g", "daemon off;"]
