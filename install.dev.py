import os
import sys
import subprocess

def run_command(command):
    try:
        subprocess.run(command, shell=True, check=True)
    except subprocess.CalledProcessError:
        print(f"❌ Failed to run: {command}")
        sys.exit(1)

pwd_variable = "%cd%" if os.name == "nt" else "$(pwd)"

print("Installing Composer dependencies...")
composer_cmd = f'docker run --rm -v "{pwd_variable}":/app -w /app composer install --ignore-platform-reqs'
run_command(composer_cmd)

print("\nInstalling NPM dependencies...")
npm_cmd = f'docker run --rm -v "{pwd_variable}":/app -w /app node:lts-alpine npm install'
run_command(npm_cmd)

print("\nAll dependencies installed successfully.")
