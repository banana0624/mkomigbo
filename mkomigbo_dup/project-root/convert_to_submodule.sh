#!/usr/bin/env bash
set -e

# --- Configuration: adjust these paths/URLs to your setup ---
PARENT_DIR="$(pwd)"  # should be project-root
SUBFOLDER="mkomigbo"
NEW_REPO_URL="https://github.com/banana0624/mkomigbo.git"
NEW_REPO_DIR="mkomigbo-newrepo"
BRANCH_NAME="launch-push"
PARENT_BRANCH="main"

# --- Step 0: sanity checks ---
if [ ! -d "$PARENT_DIR/$SUBFOLDER" ]; then
  echo "Error: folder '$SUBFOLDER' not found in parent directory. Aborting."
  exit 1
fi
if [ ! -d "$PARENT_DIR/.git" ]; then
  echo "Error: parent directory is not a Git repo (no .git). Aborting."
  exit 1
fi

# 1. Backup parent (branch)
echo "Creating parent backup branch: backup-before-submodule"
git branch -f backup-before-submodule

# 2. Clone the subfolder content as a new repo
cd "$PARENT_DIR"
git clone "$SUBFOLDER" "$NEW_REPO_DIR"

cd "$NEW_REPO_DIR"
git checkout "$BRANCH_NAME" || git checkout -b "$BRANCH_NAME"

# 3. Filter-branch to isolate only content (rewrite history)
echo "Filtering branch to subdirectory..."
git filter-branch --subdirectory-filter . -- --all

# 4. Remove origin, add new remote
git remote remove origin || true
git remote add origin "$NEW_REPO_URL"

# 5. Push filtered repo to new remote branch
echo "Pushing to new remote repository ($NEW_REPO_URL) on branch $BRANCH_NAME"
git push -u origin "$BRANCH_NAME"

# 6. Return to parent
cd "$PARENT_DIR"

# 7. Remove from parent tracking (but keep files)
git rm -r --cached "$SUBFOLDER"

# 8. Add submodule under the same folder path
git submodule add -b "$BRANCH_NAME" "$NEW_REPO_URL" "$SUBFOLDER"

# 9. Commit changes in parent
git add .gitmodules "$SUBFOLDER"
git commit -m "Convert $SUBFOLDER folder to submodule (branch $BRANCH_NAME)"

# 10. Push parent changes
git push origin "$PARENT_BRANCH"

echo "Submodule conversion finished successfully."
