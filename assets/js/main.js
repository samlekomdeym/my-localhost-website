// Main JavaScript functionality
document.addEventListener("DOMContentLoaded", () => {
  // Sembunyikan loading spinner segera setelah DOMContentLoaded
  window.CampusApp.hideLoading('loading-spinner');

  // Mobile menu toggle
  const mobileMenuBtn = document.querySelector(".mobile-menu-btn");
  const sidebar = document.querySelector(".sidebar");

  if (mobileMenuBtn && sidebar) {
    mobileMenuBtn.addEventListener("click", () => {
      sidebar.classList.toggle("active");
    });
  }

  // Close mobile menu when clicking outside
  document.addEventListener("click", (e) => {
    if (sidebar && sidebar.classList.contains("active")) {
      if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
        sidebar.classList.remove("active");
      }
    }
  });

  // Smooth scrolling for anchor links
  const anchorLinks = document.querySelectorAll('a[href^="#"]');
  anchorLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });

  // Loading spinner for forms (ini akan dipicu oleh form submit)
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", () => {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        // Hanya ubah teks dan disable tombol, loading spinner global sudah ada
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
        submitBtn.disabled = true;
      }
      // Tampilkan spinner global saat form disubmit
      window.CampusApp.showLoading('loading-spinner'); 
    });
  });

  // Image preview for file uploads
  const fileInputs = document.querySelectorAll('input[type="file"]');
  fileInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const file = this.files[0];
      if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = (e) => {
          let preview = document.querySelector(".image-preview");
          if (!preview) {
            preview = document.createElement("img");
            preview.className = "image-preview";
            preview.style.maxWidth = "200px";
            preview.style.marginTop = "10px";
            input.parentNode.appendChild(preview);
          }
          preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  });

  // Initialize application
  initializeApp();
});

// Variabel global dari PHP, diakses via window object
const SITE_URL_JS = window.SITE_URL || ''; 
const IS_LOGGED_IN_JS = window.IS_LOGGED_IN || false;
const USER_ROLE_JS = window.USER_ROLE || '';

// Deklarasi array notifikasi
let notifications = []; 


function initializeApp() {
  // Initialize Bootstrap components
  initializeBootstrap();

  // Initialize notifications, hanya jika user login
  if (IS_LOGGED_IN_JS) {
    initializeNotifications();
  }

  // Initialize search functionality
  initializeSearch();

  // Initialize form validation
  initializeFormValidation();

  // Initialize data tables
  initializeDataTables();

  // Initialize theme
  initializeTheme();

  console.log("Campus Management System initialized");
}

// Bootstrap initialization
function initializeBootstrap() {
  // Inisialisasi Tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new window.bootstrap.Tooltip(tooltipTriggerEl));

  // Inisialisasi Popovers
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
  var popoverList = popoverTriggerList.map((popoverTriggerEl) => new window.bootstrap.Popover(popoverTriggerEl));

  // Inisialisasi Modals
  var modalElements = document.querySelectorAll(".modal");
  modalElements.forEach((modalEl) => {
    if (!modalEl.dataset.bsInitialized) {
      new window.bootstrap.Modal(modalEl);
      modalEl.dataset.bsInitialized = true;
    }
  });

  // Inisialisasi Dropdown Bootstrap
  var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
  var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
    return new window.bootstrap.Dropdown(dropdownToggleEl)
  });
}

// Notifications system
function initializeNotifications() {
  loadNotifications();

  // Check for new notifications every 30 seconds
  setInterval(loadNotifications, 30000);
}

function loadNotifications() {
  fetch(`${SITE_URL_JS}/api/notifications.php?action=get_notifications`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status} for ${response.url}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data && data.success) {
        notifications = data.data || []; 
        updateNotificationUI(data.unread_count);
      } else {
        console.error("API response indicates failure or malformed data:", data);
      }
    })
    .catch((error) => {
      console.error("Error loading notifications:", error);
    });
}

function updateNotificationUI(unreadCount) {
  const notificationBadge = document.querySelector('.navbar .badge'); 
  const notificationList = document.querySelector('.notification-dropdown'); 

  if (notificationBadge) {
    if (unreadCount > 0) {
      notificationBadge.textContent = unreadCount > 99 ? "99+" : unreadCount;
      notificationBadge.style.display = "inline-block"; 
    } else {
      notificationBadge.style.display = "none";
    }
  }

  if (notificationList) {
    notificationList.innerHTML = "";

    if (notifications.length === 0) {
      notificationList.innerHTML = '<li class="dropdown-item-text text-center text-muted py-3">Tidak ada notifikasi</li>'; 
    } else {
      notifications.slice(0, 5).forEach((notification) => {
        const item = createNotificationItem(notification);
        notificationList.appendChild(item);
      });

      if (notifications.length > 5) {
        const viewAllItem = document.createElement("li"); 
        viewAllItem.innerHTML = `<a class="dropdown-item text-center" href="${SITE_URL_JS}/notifications.php">Lihat Semua</a>`; 
        notificationList.appendChild(viewAllItem);
      }
    }
  }
}

function createNotificationItem(notification) {
  const item = document.createElement("li"); 
  item.className = ``; 
  item.innerHTML = `
        <a class="dropdown-item notification-item ${notification.is_read === 0 ? 'unread' : ''}" 
           href="#" data-notification-id="${notification.id}">
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${window.CampusApp.truncateText(notification.message, 60)}</div>
                <div class="notification-time">${window.CampusApp.formatDateTime(notification.created_at)}</div>
            </div>
            ${notification.is_read === 0 ? '<div class="notification-indicator"></div>' : ''}
        </a>
    `;

  const anchorTag = item.querySelector('a');
  if (anchorTag) {
    anchorTag.addEventListener("click", (e) => {
      e.preventDefault(); 
      markNotificationAsRead(notification.id);
    });
  }

  return item;
}

function getNotificationIcon(type) {
  const icons = {
    info: "info-circle",
    warning: "exclamation-triangle",
    success: "check-circle",
    error: "times-circle",
  };
  return icons[type] || "bell";
}

function getNotificationColor(type) {
  const colors = {
    info: "info",
    warning: "warning",
    success: "success",
    error: "danger",
  };
  return colors[type] || "primary";
}

function markNotificationAsRead(notificationId) {
  fetch(`${SITE_URL_JS}/api/notifications.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ id: notificationId, action: 'mark_read' }),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status} for ${response.url}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        loadNotifications(); 
      }
    })
    .catch((error) => {
      console.error("Error marking notification as read:", error);
    });
}

// Search functionality
function initializeSearch() {
  const searchInput = document.getElementById("globalSearch");
  const searchResults = document.getElementById("searchResults");

  if (searchInput && searchResults) {
    let searchTimeout;

    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout);
      const query = this.value.trim();

      if (query.length < 2) {
        searchResults.style.display = "none";
        return;
      }

      searchTimeout = setTimeout(() => {
        performSearch(query);
      }, 300);
    });

    // Hide search results when clicking outside
    document.addEventListener("click", (e) => {
      if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = "none";
      }
    });
  }
}

function performSearch(query) {
  const searchResults = document.getElementById("searchResults");

  window.CampusApp.showLoading('loading-spinner'); 

  fetch(`${SITE_URL_JS}/api/search.php?q=${encodeURIComponent(query)}&limit=10`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status} for ${response.url}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        displaySearchResults(data.data);
      } else {
        showSearchError();
      }
    })
    .catch((error) => {
      console.error("Search error:", error);
      showSearchError();
    })
    .finally(() => { 
        window.CampusApp.hideLoading('loading-spinner'); 
    });
}

function displaySearchResults(results) {
  const searchResults = document.getElementById("searchResults");

  if (results.length === 0) {
    searchResults.innerHTML = '<div class="dropdown-item text-center text-muted">Tidak ada hasil ditemukan</div>';
  } else {
    searchResults.innerHTML = "";

    results.forEach((result) => {
      const item = createSearchResultItem(result);
      searchResults.appendChild(item);
    });
  }

  searchResults.style.display = "block";
}

function createSearchResultItem(result) {
  const item = document.createElement("a");
  item.className = "dropdown-item";
  item.href = getResultUrl(result);

  const icon = getResultIcon(result.type);
  const title = getResultTitle(result);
  const subtitle = getResultSubtitle(result);

  item.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${icon} me-3 text-muted"></i>
            <div>
                <div class="fw-bold">${title}</div>
                <small class="text-muted">${subtitle}</small>
            </div>
        </div>
    `;

  return item;
}

function getResultUrl(result) {
  const urls = {
    mahasiswa: `${SITE_URL_JS}/admin/mahasiswa/view.php?id=${result.id}`,
    dosen: `${SITE_URL_JS}/admin/dosen/view.php?id=${result.id}`,
    mata_kuliah: `${SITE_URL_JS}/admin/academic/mata-kuliah.php?id=${result.id}`,
    info: `${SITE_URL_JS}/pages/info.php?id=${result.id}`,
  };
  return urls[result.type] || "#";
}

function getResultIcon(type) {
  const icons = {
    mahasiswa: "user-graduate",
    dosen: "chalkboard-teacher",
    mata_kuliah: "book",
    info: "info-circle",
  };
  return icons[type] || "search";
}

function getResultTitle(result) {
  switch (result.type) {
    case "mahasiswa":
      return result.nama_lengkap;
    case "dosen":
      return result.nama_lengkap;
    case "mata_kuliah":
      return result.nama_mk;
    case "info":
      return result.judul;
    default:
      return "Unknown";
  }
}

function getResultSubtitle(result) {
  switch (result.type) {
    case "mahasiswa":
      return `${result.nim} - ${result.program_studi}`;
    case "dosen":
      return `${result.nidn} - ${result.jabatan}`;
    case "mata_kuliah":
      return `${result.kode_mk} - ${result.sks} SKS`;
    case "info":
      return window.CampusApp.formatDate(result.created_at);
    default:
      return "";
  }
}

function showSearchError() {
  const searchResults = document.getElementById("searchResults");
  searchResults.innerHTML = '<div class="dropdown-item text-center text-danger">Terjadi kesalahan saat mencari</div>';
  searchResults.style.display = "block";
}

// Form validation
function initializeFormValidation() {
  const forms = document.querySelectorAll(".needs-validation");

  forms.forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }

      form.classList.add("was-validated");
    });
  });

  // Real-time validation
  const inputs = document.querySelectorAll("input[required], select[required], textarea[required]");
  inputs.forEach((input) => {
    input.addEventListener("blur", function () {
      validateField(this);
    });

    input.addEventListener("input", function () {
      if (this.classList.contains("is-invalid")) {
        validateField(this);
      }
    });
  });
}

function validateField(field) {
  const isValid = field.checkValidity();

  field.classList.remove("is-valid", "is-invalid");
  field.classList.add(isValid ? "is-valid" : "is-invalid");

  // Show/hide custom error message
  const errorElement = field.parentNode.querySelector(".invalid-feedback");
  if (errorElement) {
    errorElement.style.display = isValid ? "none" : "block";
  }
}

// Data tables functionality
function initializeDataTables() {
  // Add sorting functionality
  const sortableHeaders = document.querySelectorAll(".table th[data-sort]");
  sortableHeaders.forEach((header) => {
    header.style.cursor = "pointer";
    header.addEventListener("click", function () {
      sortTable(this);
    });
  });

  // Add row selection functionality
  const selectAllCheckbox = document.getElementById("selectAll");
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", function () {
      const checkboxes = document.querySelectorAll(".row-checkbox");
      checkboxes.forEach((checkbox) => {
        checkbox.checked = this.checked;
      });
      updateBulkActions();
    });
  }

  const rowCheckboxes = document.querySelectorAll(".row-checkbox");
  rowCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updateBulkActions);
  });
}

function sortTable(header) {
  const table = header.closest("table");
  const tbody = table.querySelector("tbody");
  const rows = Array.from(tbody.querySelectorAll("tr"));
  const columnIndex = Array.from(header.parentNode.children).indexOf(header);
  const sortDirection = header.dataset.sort === "asc" ? "desc" : "asc";

  // Update sort indicators
  table.querySelectorAll("th[data-sort]").forEach((th) => {
    th.classList.remove("sort-asc", "sort-desc");
  });
  header.classList.add(`sort-${sortDirection}`);
  header.dataset.sort = sortDirection;

  // Sort rows
  rows.sort((a, b) => {
    const aValue = a.children[columnIndex].textContent.trim();
    const bValue = b.children[columnIndex].textContent.trim();

    // Try to parse as numbers
    const aNum = Number.parseFloat(aValue);
    const bNum = Number.parseFloat(bValue);

    if (!isNaN(aNum) && !isNaN(bNum)) {
      return sortDirection === "asc" ? aNum - bNum : bNum - aNum;
    }

    // String comparison
    return sortDirection === "asc" ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
  });

  // Reorder rows in DOM
  rows.forEach((row) => tbody.appendChild(row));
}

function updateBulkActions() {
  const selectedCheckboxes = document.querySelectorAll(".row-checkbox:checked");
  const bulkActions = document.getElementById("bulkActions");

  if (bulkActions) {
    bulkActions.style.display = selectedCheckboxes.length > 0 ? "block" : "none";
  }

  // Update select all checkbox state
  const selectAllCheckbox = document.getElementById("selectAll");
  const allCheckboxes = document.querySelectorAll(".row-checkbox");

  if (selectAllCheckbox && allCheckboxes.length > 0) {
    selectAllCheckbox.indeterminate = selectedCheckboxes.length > 0 && selectedCheckboxes.length < allCheckboxes.length;
    selectAllCheckbox.checked = selectedCheckboxes.length === allCheckboxes.length;
  }
}

// Tooltips and popovers
function initializeTooltips() {
  // Reinitialize tooltips for dynamically added content
  const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltips.forEach((tooltip) => {
    new window.bootstrap.Tooltip(tooltip);
  });

  const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
  popovers.forEach((popover) => {
    new window.bootstrap.Popover(popover);
  });
}

// Theme management
function initializeTheme() {
  const themeToggle = document.getElementById("themeToggle");
  const currentTheme = localStorage.getItem("theme") || "light";

  document.documentElement.setAttribute("data-theme", currentTheme);

  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      const currentTheme = document.documentElement.getAttribute("data-theme");
      const newTheme = currentTheme === "light" ? "dark" : "light";

      document.documentElement.setAttribute("data-theme", newTheme);
      localStorage.setItem("theme", newTheme);

      // Update theme toggle icon
      const icon = themeToggle.querySelector("i");
      if (icon) {
        icon.className = newTheme === "light" ? "fas fa-moon" : "fas fa-sun";
      }
    });
  }
}

// Utility functions (Diekspos secara global melalui window.CampusApp)
function showLoading(element) {
  // Jika element adalah ID spinner global
  if (element && typeof element === 'string') {
    const globalSpinner = document.getElementById(element);
    if (globalSpinner) {
      globalSpinner.style.display = 'flex';
      return;
    }
  }
  // Jika element adalah DOM object atau null/undefined
  if (element && element.tagName) { // Cek apakah ini elemen DOM
    element.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    element.style.display = "block";
  } else {
    // Default: tampilkan spinner global jika tidak ada elemen spesifik atau elemen tidak valid
    const globalSpinner = document.getElementById('loading-spinner');
    if (globalSpinner) {
      globalSpinner.style.display = 'flex';
    }
  }
}

function hideLoading(element) {
  if (element && typeof element === 'string') {
    const globalSpinner = document.getElementById(element);
    if (globalSpinner) {
      globalSpinner.style.display = 'none';
      return;
    }
  }
  if (element && element.tagName) { // Cek apakah ini elemen DOM
    element.innerHTML = ''; // Hapus indikator loading dari elemen spesifik
    element.style.display = "none";
  } else {
    const globalSpinner = document.getElementById('loading-spinner');
    if (globalSpinner) {
      globalSpinner.style.display = 'none';
    }
  }
}

function showAlert(message, type = "info", duration = 5000) {
  // Menggunakan fungsi showToast yang ada di footer.php jika tersedia
  // Ini adalah fungsi global, jadi panggil window.showToast jika ingin pakai yang dari footer
  if (typeof window.showToast === 'function') {
    window.showToast(message, type);
  } else {
    // Fallback jika showToast tidak tersedia
    const alertContainer = document.getElementById("alertContainer") || document.body;

    const alert = document.createElement("div");
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

    alertContainer.appendChild(alert);

    // Auto dismiss
    if (duration > 0) {
      setTimeout(() => {
        if (alert.parentNode) {
          alert.remove();
        }
      }, duration);
    }
  }
}

function confirmDelete(message = "Yakin ingin menghapus data ini?") {
  return confirm(message);
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("id-ID", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

function truncateText(text, maxLength) {
  if (text === null || typeof text === 'undefined') return ''; // Handle null/undefined text
  text = String(text); // Pastikan text adalah string
  if (text.length <= maxLength) return text;
  return text.substr(0, maxLength) + "...";
}

function exportToCSV(tableId, filename = "data.csv") {
  const table = document.getElementById(tableId);
  if (!table) return;

  const csv = [];
  const rows = table.querySelectorAll("tr");

  rows.forEach((row) => {
    const cols = row.querySelectorAll("td, th");
    const rowData = [];

    cols.forEach((col) => {
      rowData.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
    });

    csv.push(rowData.join(","));
  });

  downloadCSV(csv.join("\n"), filename);
}

function downloadCSV(csv, filename) {
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");

  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}

function printPage() {
  window.print();
}

// AJAX helper functions
function makeRequest(url, options = {}) {
  const defaultOptions = {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  };

  const finalOptions = Object.assign({}, defaultOptions, options); // Menggunakan Object.assign untuk kompatibilitas lebih luas

  return fetch(url, finalOptions).then((response) => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  });
}

function submitForm(form, callback) {
  const formData = new FormData(form);
  const url = form.action || window.location.href;

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (callback) callback(data);
    })
    .catch((error) => {
      console.error("Form submission error:", error);
      showAlert("Terjadi kesalahan saat mengirim data", "danger");
    });
}

// Global error handler
window.addEventListener("error", (e) => {
  console.error("Global error:", e.error);
  // You can send error reports to server here
});

// Expose global functions (pastikan tidak ada duplikasi dengan window. CampusApp)
window.CampusApp = {
  showAlert: showAlert,
  confirmDelete: confirmDelete,
  formatDateTime: formatDateTime,
  formatDate: formatDate,
  truncateText: truncateText,
  exportToCSV: exportToCSV,
  printPage: printPage,
  makeRequest: makeRequest,
  submitForm: submitForm,
  showLoading: showLoading, 
  hideLoading: hideLoading, 
};
