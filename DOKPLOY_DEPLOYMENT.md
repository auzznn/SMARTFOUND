# 🚀 Dokploy Deployment Guide — SmartFound

> [!IMPORTANT]
> **Zero Manual Seeding & Monorepo Path Fix**  
> 1. Database tables creation and sample data seeding are **100% automated** on startup.
> 2. **Avoid the Double-Subdirectory Bug**: Dokploy has a bug where setting the Git **Root Directory** to a subdirectory (like `/backend`) causes it to try to write the `.env` file to a duplicated path (`/backend/backend/.env`), resulting in a `Directory nonexistent` deployment crash. 
> 
> **To fix this, configure the settings as shown below (setting Git Root Directory to `/` and Dockerfile Path relative to the repo root).**

---

## 🏗️ Production Architecture

In production, the application runs entirely self-hosted on your Dokploy server.

```mermaid
graph TD
    User([User's Browser]) -->|HTTPS| Traefik[Dokploy Traefik Proxy]
    Traefik -->|smartfound.utm.my (Frontend Domain)| Frontend[Frontend Container: Vue 3 + Nginx]
    Traefik -->|api.smartfound.utm.my (Backend Domain)| Backend[Backend Container: PHP 8.2 + Apache]
    Backend -->|Persistent Storage| Volume[(Docker Volume: /var/www/html/uploads)]
    Backend -->|Internal Network| DokployDB[(Managed Dokploy PostgreSQL)]
```

---

## 🗄️ Step 1: Create the Dokploy PostgreSQL Service

You can provision a managed PostgreSQL service directly inside Dokploy.

1. Open your Dokploy Dashboard and go to **Projects** -> Select your `SmartFound` project.
2. Click **Add Service** and select **Database**.
3. Choose **PostgreSQL**. Name it `smartfound-db`.
4. Click on it to retrieve the connection credentials:
   - **Internal Host**: E.g. `smartfound-db`
   - **Internal Port**: `5432`
   - **Username**: `postgres` (or as generated)
   - **Password**: E.g., `your_generated_password`
   - **Database Name**: `postgres` (or as generated)

*(Note: There is no need to expose ports to the public internet or run any local terminal commands. The backend handles the rest automatically.)*

---

## 📦 Step 2: Deploy the Backend (PHP Slim 4 API)

The backend runs database migrations and seeding automatically upon container startup.

### 1. Configure Git Repository (under Git Provider settings)
- **Source**: Select your GitHub/Git Provider.
- **Repository**: Select `SMARTFOUND`.
- **Branch**: Select your deployment branch (e.g., `main` or `master`).
- **Root Directory**: **Set to `/`** (This ensures Dokploy writes environment variables successfully at the repository root).

### 2. Configure Docker Build Settings (under Build Settings)
- **Build Type**: Select **Dockerfile**.
- **Build Path (Context)**: **Set to `/backend`** (This points Docker's context to the backend folder).
- **Dockerfile Path**: **Set to `backend/Dockerfile`** (Dokploy resolves this relative to the repository root `/`).

### 3. Environment Variables
Add the following key-value pairs in the **Environment** tab:

| Variable Name | Production Value | Description / Purpose |
|---|---|---|
| `APP_ENV` | `production` | Enforces production routing & security rules |
| `APP_SECRET` | *Generates a 32+ char random string* | Used to sign JWTs |
| `APP_URL` | `https://api.smartfound.utm.my` | Public URL of this API |
| `FRONTEND_URL` | `https://smartfound.utm.my` | Allowed origin for CORS (Must match frontend URL!) |
| `DB_HOST` | `smartfound-db` | **Use the Internal Host** of your Dokploy Postgres service |
| `DB_PORT` | `5432` | **Use the Internal Port** of your Dokploy Postgres service |
| `DB_NAME` | `postgres` | Your Dokploy Database Name |
| `DB_USER` | `postgres` | Your Dokploy Database Username |
| `DB_PASS` | *your_generated_password* | Your Dokploy Database Password |
| `UPLOAD_DIR` | `uploads` | Directory for uploads |
| `MAX_FILE_SIZE` | `2097152` | 2MB file limit |
| `GOOGLE_CLIENT_ID` | *your_google_client_id* | Optional for Google OAuth |
| `GOOGLE_CLIENT_SECRET` | *your_google_client_secret* | Optional for Google OAuth |
| `GOOGLE_REDIRECT_URI` | `https://api.smartfound.utm.my/api/v1/auth/google/callback` | Google OAuth redirect endpoint |

### 4. Persistent Volume Mount
We must persist the `/var/www/html/uploads` folder so uploaded photos aren't deleted when the container restarts.
1. Go to the **Volumes** tab.
2. Click **Add Volume**.
3. **Mount Path**: Set to `/var/www/html/uploads`.
4. **Volume Name**: Set to `backend-uploads`.

### 5. Domain Configuration
1. Go to the **Domains** tab.
2. Add your backend API subdomain (e.g., `api.smartfound.utm.my`).
3. Set the **Container Port** to `80`.
4. Enable **HTTPS (Let's Encrypt)**.

### 6. Automatic Migrations Check
When you deploy `smartfound-backend`, Dokploy will build the image and run `docker-entrypoint.sh` on startup. The container will automatically:
- Wait/retry connection to the `smartfound-db` service.
- Check if the database has tables (using the `users` table as reference).
- If empty, it automatically applies `database/schema.sql` and `database/seed.sql`.
- If tables already exist, it skips migrations to protect user data.

---

## 🎨 Step 3: Deploy the Frontend (Vue 3 SPA)

### 1. Configure Git Repository (under Git Provider settings)
- **Source**: Select your GitHub/Git Provider.
- **Repository**: Select `SMARTFOUND`.
- **Branch**: Select your deployment branch.
- **Root Directory**: **Set to `/`** (This ensures Dokploy writes environment variables successfully).

### 2. Configure Docker Build Settings (under Build Settings)
- **Build Type**: Select **Dockerfile**.
- **Build Path (Context)**: **Set to `/frontend`** (This points Docker's context to the frontend folder).
- **Dockerfile Path**: **Set to `frontend/Dockerfile`** (Dokploy resolves this relative to the repository root `/`).

### 3. Build-Time Arguments (CRITICAL FOR VITE)
In Dokploy, go to the application settings, click the **Environment** tab, find **Build Arguments**, and add:

| Argument Name | Production Value | Purpose |
|---|---|---|
| `VITE_API_BASE_URL` | `https://api.smartfound.utm.my/api/v1` | URL of the backend deployed in Step 2 |
| `VITE_GOOGLE_CLIENT_ID` | *your_google_client_id* | Google OAuth Client ID |

### 4. Domain Configuration
1. Go to the **Domains** tab.
2. Add your main frontend domain (e.g., `smartfound.utm.my`).
3. Set the **Container Port** to `80`.
4. Enable **HTTPS (Let's Encrypt)**.

---

## 🐳 Step 4: Alternative Deployment (Docker Compose)

If you prefer to deploy both services together under a single Compose service in Dokploy using the root [docker-compose.yml](file:///Users/macbook/Documents/utm/semester%206/web-tech/final_proj/SMARTFOUND/docker-compose.yml):

1. In Dokploy, go to **Add Service** and select **Compose**.
2. Name it `smartfound-stack`.
3. Set up the Git credentials pointing to the root of your repository.
4. Add all environment variables in the environment manager. Dokploy will inject them into the Compose container environments automatically.
5. In the Dokploy interface, configure domain routing:
   - Route `smartfound.utm.my` to service `frontend` on port `80`.
   - Route `api.smartfound.utm.my` to service `backend` on port `80` (mapped to `80` inside the container).

*(Note: The Compose deployment also runs database initialization automatically, provisioning its own self-contained database.)*

---

## 🔒 Post-Deployment Security & Google OAuth Checklist

1. **Google Cloud Console Update**:
   - Go to [Google API Console](https://console.cloud.google.com/).
   - Update your OAuth credentials.
   - Add `https://smartfound.utm.my` to **Authorized JavaScript Origins**.
   - Add `https://api.smartfound.utm.my/api/v1/auth/google/callback` to **Authorized redirect URIs**.

2. **CORS Validation**:
   - Ensure the backend environment variable `FRONTEND_URL` exactly matches the domain of the frontend (e.g., `https://smartfound.utm.my`).

---

## 🛠️ Troubleshooting

- **Error: `404 Not Found` when reloading frontend pages**:
  - Ensure [nginx.conf](file:///Users/macbook/Documents/utm/semester%206/web-tech/final_proj/SMARTFOUND/frontend/nginx.conf) has `try_files $uri $uri/ /index.html;` loaded inside the frontend container.
- **Error: `Network Error` or CORS issues on login**:
  - Confirm the API base URL is HTTPS: `https://api.smartfound.utm.my/api/v1`.
  - Check that `FRONTEND_URL` in the backend environment variables is spelled correctly and includes `https://`.
- **Uploaded images show broken links**:
  - Check that the backend container's `/var/www/html/uploads` folder is correctly writeable (`chown -R www-data:www-data uploads`).
  - Make sure the persistent volume is active and mounted correctly.
