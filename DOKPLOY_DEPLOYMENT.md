# 🚀 Dokploy Deployment Guide — SmartFound

This guide outlines the step-by-step process for deploying the **SmartFound Lost & Found Platform** onto **Dokploy** (a self-hosted PaaS alternative to Heroku/Coolify).

---

## 🏗️ Production Architecture

In production, the application is split into two Dockerized containers managed by Dokploy's Traefik reverse proxy. It connects to your remote database (e.g., Supabase PostgreSQL).

```mermaid
graph TD
    User([User's Browser]) -->|HTTPS| Traefik[Dokploy Traefik Proxy]
    Traefik -->|smartfound.utm.my (Frontend Domain)| Frontend[Frontend Container: Vue 3 + Nginx]
    Traefik -->|api.smartfound.utm.my (Backend Domain)| Backend[Backend Container: PHP 8.2 + Apache]
    Backend -->|Persistent Storage| Volume[(Docker Volume: /var/www/html/uploads)]
    Backend -->|PDO Database Connection| Supabase[(Supabase PostgreSQL)]
```

---

## 🗄️ Step 1: Database Verification (Supabase)

Since your schema is already compatible with Supabase PostgreSQL, ensure that:
1. Your database tables are set up using `backend/database/schema.sql`.
2. You have your Supabase DB Connection details ready (Host, Port, Database Name, User, and Password).

---

## 📦 Step 2: Deploying the Backend (PHP Slim 4 API)

We will deploy the backend as an **Application** in Dokploy.

### 1. Create the Application in Dokploy
1. Open your Dokploy Dashboard and go to **Projects**.
2. Click **Create Project** (e.g., `SmartFound`).
3. Click **Add Service** and select **Application**. Name it `smartfound-backend`.

### 2. Configure Git Repository
- **Source**: Select your GitHub/Git Provider.
- **Repository**: Select `SMARTFOUND`.
- **Branch**: Select your deployment branch (e.g., `main` or `master`).
- **Root Directory**: Set to `/backend`.

### 3. Build Settings
- **Build Type**: Select **Dockerfile**.
- **Dockerfile Path**: Set to `Dockerfile` (relative to the `/backend` root directory).

### 4. Environment Variables
Add the following key-value pairs in the **Environment** tab:

| Variable Name | Example Production Value | Purpose |
|---|---|---|
| `APP_ENV` | `production` | Enforces production routing & security rules |
| `APP_SECRET` | *Generates a 32+ char random string* | Used to sign JWTs |
| `APP_URL` | `https://api.smartfound.utm.my` | Public URL of this API |
| `FRONTEND_URL` | `https://smartfound.utm.my` | Allowed origin for CORS (Must match frontend URL!) |
| `DB_HOST` | `db.ulvytmzmtsujzcagrwaj.supabase.co` | Supabase DB Host |
| `DB_PORT` | `5432` | Postgres Port |
| `DB_NAME` | `postgres` | Postgres Database Name |
| `DB_USER` | `postgres.ulvytmzmtsujzcagrwaj` | Postgres Username |
| `DB_PASS` | *your_supabase_password* | Postgres Password |
| `UPLOAD_DIR` | `uploads` | Directory for uploads |
| `MAX_FILE_SIZE` | `2097152` | 2MB file limit |
| `GOOGLE_CLIENT_ID` | *your_google_client_id* | Optional for Google OAuth |
| `GOOGLE_CLIENT_SECRET` | *your_google_client_secret* | Optional for Google OAuth |
| `GOOGLE_REDIRECT_URI` | `https://api.smartfound.utm.my/api/v1/auth/google/callback` | Google OAuth redirect endpoint |

### 5. Persistent Volume Mount (CRITICAL FOR UPLOADS)
Since users upload photos of lost/found items, we must persist the `/var/www/html/uploads` folder so images aren't deleted when the container restarts or redeplys.
1. Go to the **Volumes** tab.
2. Click **Add Volume**.
3. **Mount Path**: Set to `/var/www/html/uploads`.
4. **Volume Name**: Set to `backend-uploads` (or let Dokploy generate it).

### 6. Domain Configuration
1. Go to the **Domains** tab.
2. Add your backend API subdomain (e.g., `api.smartfound.utm.my`).
3. Set the **Container Port** to `80`.
4. Enable **HTTPS (Let's Encrypt)**.

---

## 🎨 Step 3: Deploying the Frontend (Vue 3 SPA)

We will deploy the frontend as a separate **Application** in Dokploy.

### 1. Create the Application in Dokploy
1. In the same project, click **Add Service** and select **Application**. Name it `smartfound-frontend`.

### 2. Configure Git Repository
- **Source**: Select your GitHub/Git Provider.
- **Repository**: Select `SMARTFOUND`.
- **Branch**: Select your deployment branch.
- **Root Directory**: Set to `/frontend`.

### 3. Build Settings
- **Build Type**: Select **Dockerfile**.
- **Dockerfile Path**: Set to `Dockerfile` (relative to the `/frontend` root directory).

### 4. Build-Time Arguments (CRITICAL FOR VITE)
> [!IMPORTANT]
> Vite bakes environment variables into the compiled JavaScript bundle **at build time**. Standard runtime environment variables will NOT work. You must define these as **Build Arguments** in Dokploy.

In Dokploy, go to **Build Settings** or **Environment** and add these under **Build Arguments** (or `ARG` inputs):

| Argument Name | Production Value | Purpose |
|---|---|---|
| `VITE_API_BASE_URL` | `https://api.smartfound.utm.my/api/v1` | URL of the backend deployed in Step 2 |
| `VITE_GOOGLE_CLIENT_ID` | *your_google_client_id* | Google OAuth Client ID |

### 5. Domain Configuration
1. Go to the **Domains** tab.
2. Add your main frontend domain (e.g., `smartfound.utm.my`).
3. Set the **Container Port** to `80`.
4. Enable **HTTPS (Let's Encrypt)**.

---

## 🐳 Step 4: Alternative Deployment (Docker Compose)

If you prefer to deploy both services together under a single service dashboard on Dokploy using the root `docker-compose.yml`:

1. In Dokploy, go to **Add Service** and select **Compose**.
2. Name it `smartfound-stack`.
3. Set up the Git credentials pointing to the root of your repository.
4. Add all environment variables in the environment manager. Dokploy will inject them into the Compose container environments automatically.
5. In the Dokploy interface, configure domain routing:
   - Route `smartfound.utm.my` to service `frontend` on port `80`.
   - Route `api.smartfound.utm.my` to service `backend` on port `80` (mapped to `80` inside the container).

---

## 🔒 Post-Deployment Security & Google OAuth Checklist

1. **Google Cloud Console Update**:
   - Go to [Google API Console](https://console.cloud.google.com/).
   - Update your OAuth credentials.
   - Add `https://smartfound.utm.my` to **Authorized JavaScript Origins**.
   - Add `https://api.smartfound.utm.my/api/v1/auth/google/callback` to **Authorized redirect URIs**.

2. **CORS Validation**:
   - Ensure the backend environment variable `FRONTEND_URL` exactly matches the domain of the frontend (e.g., `https://smartfound.utm.my`).
   - If they differ, browser preflight requests will block logins and report listings.

3. **Check File Upload Permissions**:
   - Upload a test item image.
   - Verify it appears on the dashboard and is stored inside the mapped Docker volume.

---

## 🛠️ Troubleshooting

- **Error: `404 Not Found` when reloading frontend pages**:
  - The Vue router is trying to resolve routing server-side. Ensure `nginx.conf` has `try_files $uri $uri/ /index.html;` loaded inside the frontend container.
- **Error: `Network Error` or CORS issues on login**:
  - Confirm the API base URL is HTTPS: `https://api.smartfound.utm.my/api/v1`.
  - Check that `FRONTEND_URL` in the backend environment variables is spelled correctly and includes `https://`.
- **Uploaded images show broken links**:
  - Check that the backend container's `/var/www/html/uploads` folder is correctly writeable (`chown -R www-data:www-data uploads`).
  - Make sure the persistent volume is active and mounted correctly.
