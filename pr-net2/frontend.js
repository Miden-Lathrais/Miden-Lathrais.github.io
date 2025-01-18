// Select DOM elements
const uploadForm = document.getElementById("uploadForm");
const fileInput = document.getElementById("fileInput");
const fileList = document.getElementById("fileList");
const pathNavigation = document.getElementById("currentPath");
const backButton = document.getElementById("backButton");
const createFolderButton = document.getElementById("createFolder");
const folderNameInput = document.getElementById("folderName");
const fileOptionsModal = new bootstrap.Modal(document.getElementById("fileOptionsModal"));
const fileOptionsForm = document.getElementById("fileOptionsForm");
const newFileNameInput = document.getElementById("newFileName");
const moveFilePathInput = document.getElementById("moveFilePath");
const deleteFileButton = document.getElementById("deleteFileButton");

// Backend URL
const backendUrl = "backend.php";
let currentPath = ""; // Current path in the file manager

// Fetch and render the file list
async function fetchFileList() {
  try {
    const response = await fetch(`${backendUrl}?currentPath=${encodeURIComponent(currentPath)}`);
    if (!response.ok) {
      throw new Error("Failed to fetch file list.");
    }

    const items = await response.json();
    updateFileList(items);
    backButton.disabled = !currentPath; // Disable back button at root
  } catch (error) {
    alert("Error fetching file list.");
  }
}

// Update the DOM with the file/folder list
function updateFileList(items) {
  fileList.innerHTML = "<h2 class='mb-3'>Files & Folders</h2>";
  if (items.length === 0) {
    fileList.innerHTML += "<p>No files or folders found.</p>";
    return;
  }

  items.forEach((item) => {
    const fileItem = document.createElement("div");
    fileItem.classList.add(
      "d-flex",
      "justify-content-between",
      "align-items-center",
      "border",
      "p-2",
      "mb-2",
      "rounded",
      item.isDir ? "folder-item" : "file-item"
    );

    const icon = item.isDir ? "📁" : "📄";
    fileItem.innerHTML = `
      <div>
        ${icon} <span>${item.name}</span>
      </div>
      <button class="btn btn-${item.isDir ? "primary" : "secondary"} btn-sm" 
        onclick="${item.isDir ? `navigateToFolder('${item.name}')` : `openFileOptions('${item.name}')`}">
        ${item.isDir ? "Open" : "..." }
      </button>
    `;
    fileList.appendChild(fileItem);
  });
}

// Navigate to a folder
function navigateToFolder(folderName) {
  currentPath = currentPath ? `${currentPath}/${folderName}` : folderName;
  pathNavigation.textContent = `Root/${currentPath}`;
  fetchFileList();
}

// Navigate back to the parent folder
function navigateBack() {
  const pathSegments = currentPath.split("/");
  pathSegments.pop();
  currentPath = pathSegments.join("/");
  pathNavigation.textContent = currentPath ? `Root/${currentPath}` : "Root";
  fetchFileList();
}

// Create a new folder
createFolderButton.addEventListener("click", async () => {
  const folderName = folderNameInput.value.trim();
  if (!folderName) {
    alert("Please enter a folder name.");
    return;
  }

  try {
    const formData = new FormData();
    formData.append("action", "createFolder");
    formData.append("folderName", folderName);
    formData.append("currentPath", currentPath);

    const response = await fetch(backendUrl, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();
    alert(result.message);
    fetchFileList();
    folderNameInput.value = "";
  } catch (error) {
    alert("Error creating folder.");
  }
});

// Handle file upload
uploadForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const file = fileInput.files[0];
  if (!file) {
    alert("Please select a file.");
    return;
  }

  try {
    const formData = new FormData();
    formData.append("action", "uploadFile");
    formData.append("file", file);
    formData.append("currentPath", currentPath);

    const response = await fetch(backendUrl, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();
    alert(result.message);
    fetchFileList();
    fileInput.value = ""; // Clear input
  } catch (error) {
    alert("Error uploading file.");
  }
});

// Open file options modal
let selectedFileName = "";

function openFileOptions(fileName) {
  selectedFileName = fileName;
  newFileNameInput.value = "";
  moveFilePathInput.value = "";
  fileOptionsModal.show();
}

// Rename or move file
fileOptionsForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const newFileName = newFileNameInput.value.trim();
  const moveFilePath = moveFilePathInput.value.trim();

  try {
    if (newFileName) {
      const formData = new FormData();
      formData.append("action", "renameFile");
      formData.append("currentPath", currentPath);
      formData.append("oldName", selectedFileName);
      formData.append("newName", newFileName);

      const response = await fetch(backendUrl, {
        method: "POST",
        body: formData,
      });

      const result = await response.json();
      alert(result.message);
    }

    if (moveFilePath) {
      const formData = new FormData();
      formData.append("action", "moveFile");
      formData.append("currentPath", currentPath);
      formData.append("fileName", selectedFileName);
      formData.append("destinationFolder", moveFilePath);

      const response = await fetch(backendUrl, {
        method: "POST",
        body: formData,
      });

      const result = await response.json();
      alert(result.message);
    }

    fetchFileList();
    fileOptionsModal.hide();
  } catch (error) {
    alert("Error applying file changes.");
  }
});

// Delete file
deleteFileButton.addEventListener("click", async () => {
  try {
    const response = await fetch(backendUrl, {
      method: "DELETE", // Change to DELETE method
      body: JSON.stringify({ 
        action: "deleteFile",  // Add the action parameter to indicate delete
        currentPath,
        fileName: selectedFileName
      }),
      headers: {
        "Content-Type": "application/json",
      },
    });

    const result = await response.json();
    alert(result.message);
    fetchFileList();
    fileOptionsModal.hide();
  } catch (error) {
    alert("Error deleting file.");
  }
});

// Initial fetch of files
fetchFileList();
