# 🚀 Dokploy Deployment Guide — SmartFound

This guide outlines the step-by-step process for deploying the **SmartFound Lost & Found Platform** onto **Dokploy**, utilizing Dokploy's **built-in managed PostgreSQL service** instead of Supabase.

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

## 🗄️ Step 1: Create and Seed the Dokploy PostgreSQL Service

Instead of creating a Supabase account, you can spin up a database in Dokploy with a single click.

### 1. Provision the Database
1. Open your Dokploy Dashboard and go to **Projects** -> Select your `SmartFound` project.
2. Click **Add Service** and select **Database**.
3. Choose **PostgreSQL**. Name it `smartfound-db`.
4. Once created, click on it to see the connection credentials:
   - **Internal Host**: E.g. `smartfound-db`
   - **Internal Port**: `5432`
   - **Username**: `postgres` (or as generated)
   - **Password**: E.g., `your_generated_password`
   - **Database Name**: `postgres` (or as generated)

### 2. Expose Port for Seeding (Temporary)
To run your local schema and seed SQL files on this new database:
1. In the database's settings in Dokploy, find the **Expose Port** option.
2. Set it to expose port `5432` (or a random external port like `5433` if `5432` is taken on the server host).
3. Save changes.

### 3. Run Schema & Seed from your Local Machine
Open a terminal on your local machine, and run the following commands (replace `<VPS_IP>`, `<EXTERNAL_PORT>`, `<USER>`, `<DB_NAME>`, and `<PASSWORD>` with your Dokploy database credentials):

```bash
# Set password variable so psql doesn't prompt you
export PGPASSWORD="your_generated_password"

# 1. Run the database schema to build tables
psql -h <VPS_IP> -p <EXTERNAL_PORT> -U <USER> -d <DB_NAME> -f backend/database/schema.sql

# 2. Run the database seed to insert demo data
psql -h <VPS_IP> -p <EXTERNAL_PORT> -U <USER> -d <DB_NAME> -f backend/database/seed.sql
```

> [!TIP]
> After seeding completes, you can turn off the **Expose Port** option in Dokploy for better security. The backend container communicates with the database over Dokploy's private internal network, so it doesn't need the database port exposed to the public internet!

---

## 📦 Step 2: Deploying the Backend (PHP Slim 4 API)

We will deploy the backend as an **Application** in Dokploy.

### 1. Create the Application
1. In your `SmartFound` project, click **Add Service** -> **Application**. Name it `smartfound-backend`.
2. **Git Repository**: Select `SMARTFOUND` and set the branch to deploy.
3. **Root Directory**: Set to `/backend`.
4. **Build Type**: Select **Dockerfile**.
5. **Dockerfile Path**: Set to `Dockerfile` (relative to the `/backend` root directory).

### 2. Environment Variables
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

### 3. Persistent Volume Mount
Users upload photos of lost/found items. We must persist the `/var/www/html/uploads` folder so images aren't deleted when the container restarts.
1. Go to the **Volumes** tab.
2. Click **Add Volume**.
3. **Mount Path**: Set to `/var/www/html/uploads`.
4. **Volume Name**: Set to `backend-uploads`.

### 4. Domain Configuration
1. Go to the **Domains** tab.
2. Add your backend API subdomain (e.g., `api.smartfound.utm.my`).
3. Set the **Container Port** to `80` (since Apache inside the container listens on port 80).
4. Enable **HTTPS (Let's Encrypt)**.

---

## 🎨 Step 3: Deploying the Frontend (Vue 3 SPA)

We will deploy the frontend as a separate **Application** in Dokploy.

### 1. Create the Application
1. In the same project, click **Add Service** -> **Application**. Name it `smartfound-frontend`.
2. **Git Repository**: Select `SMARTFOUND` and set the branch to deploy.
3. **Root Directory**: Set to `/frontend`.
4. **Build Type**: Select **Dockerfile**.
5. **Dockerfile Path**: Set to `Dockerfile` (relative to the `/frontend` root directory).

### 2. Build-Time Arguments (CRITICAL FOR VITE)
> [!IMPORTANT]
> Vite injects environment variables into the compiled JavaScript bundle **at build time**. Standard runtime environment variables will NOT work. You must define these as **Build Arguments** in Dokploy.

In Dokploy, go to the application settings, click the **Environment** tab, find **Build Arguments**, and add:

| Argument Name | Production Value | Purpose |
|---|---|---|
| `VITE_API_BASE_URL` | `https://api.smartfound.utm.my/api/v1` | URL of the backend deployed in Step 2 |
| `VITE_GOOGLE_CLIENT_ID` | *your_google_client_id* | Google OAuth Client ID (optional for demo) |

### 3. Domain Configuration
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
  - The Vue router is trying to resolve routing server-side. Ensure [nginx.conf](file:///Users/macbook/Documents/utm/semester%206/web-tech/final_proj/SMARTFOUND/frontend/nginx.conf) has `try_files $uri $uri/ /index.html;` loaded inside the frontend container.
- **Error: `Network Error` or CORS issues on login**:
  - Confirm the API base URL is HTTPS: `https://api.smartfound.utm.my/api/v1`.
  - Check that `FRONTEND_URL` in the backend environment variables is spelled correctly and includes `https://`.
- **Uploaded images show broken links**:
  - Check that the backend container's `/var/www/html/uploads` folder is correctly writeable (`chown -R www-data:www-data uploads`).
  - Make sure the persistent volume is active and mounted correctly.
