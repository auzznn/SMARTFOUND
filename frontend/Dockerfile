# --- Build Stage ---
FROM node:18-alpine AS build-stage

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy the rest of the application files
COPY . .

# Vite environment variables must be populated at build time.
# We accept them as build arguments and write them to the build process.
ARG VITE_API_BASE_URL
ARG VITE_GOOGLE_CLIENT_ID

# Build the production bundle.
# If VITE_API_BASE_URL build arg is empty, we run 'npm run build' directly so Vite falls back to the .env.production file.
# Otherwise, we inject the build arguments directly into the build environment.
RUN if [ -z "$VITE_API_BASE_URL" ]; then \
      npm run build; \
    else \
      VITE_API_BASE_URL=$VITE_API_BASE_URL VITE_GOOGLE_CLIENT_ID=$VITE_GOOGLE_CLIENT_ID npm run build; \
    fi

# --- Production Stage ---
FROM nginx:stable-alpine AS production-stage

# Copy Nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy static assets from build stage
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Expose HTTP port
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
