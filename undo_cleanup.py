import os
import shutil
import sys

def main():
    print("=== MedNova Cleanup Undo Utility ===")
    
    # Define absolute path relative to script directory
    script_dir = os.path.dirname(os.path.abspath(__file__))
    backup_dir = os.path.join(script_dir, "backup_before_cleanup")
    
    if not os.path.exists(backup_dir):
        print(f"Error: Backup directory '{backup_dir}' not found.")
        sys.exit(1)
        
    backend_backup = os.path.join(backup_dir, "backend")
    frontend_backup = os.path.join(backup_dir, "frontend")
    
    if not os.path.exists(backend_backup) and not os.path.exists(frontend_backup):
        print("Error: No backups found inside the backup directory.")
        sys.exit(1)
        
    # Restore backend
    if os.path.exists(backend_backup):
        backend_dest = os.path.join(script_dir, "backend")
        print("Restoring backend folder...")
        try:
            if os.path.exists(backend_dest):
                shutil.rmtree(backend_dest)
            shutil.copytree(backend_backup, backend_dest)
            print("Successfully restored 'backend/' folder.")
        except Exception as e:
            print(f"Failed to restore backend: {e}")
            
    # Restore frontend
    if os.path.exists(frontend_backup):
        frontend_dest = os.path.join(script_dir, "frontend")
        print("Restoring frontend folder...")
        try:
            if os.path.exists(frontend_dest):
                shutil.rmtree(frontend_dest)
            shutil.copytree(frontend_backup, frontend_dest)
            print("Successfully restored 'frontend/' folder.")
        except Exception as e:
            print(f"Failed to restore frontend: {e}")
            
    print("Undo cleanup completed successfully.")

if __name__ == "__main__":
    main()
