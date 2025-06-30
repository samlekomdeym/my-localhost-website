// Admin specific JavaScript functionality 
// Hapus import Chart karena Chart.js di-load global via CDN, bukan sebagai modul
// import { Chart } from "@/components/ui/chart" 

document.addEventListener("DOMContentLoaded", () => {   
  initializeAdminPanel(); 
}); 

function initializeAdminPanel() {   
  // Initialize sidebar   
  initializeSidebar();   
  // Initialize data tables   
  initializeAdminDataTables();   
  // Initialize modals   
  initializeAdminModals();   
  // Initialize form handlers   
  initializeAdminForms();   
  // Initialize dashboard widgets   
  initializeDashboardWidgets();   
  // Initialize bulk actions   
  initializeBulkActions();   
  console.log("Admin panel initialized"); 
} 

// Sidebar functionality 
function initializeSidebar() {   
  const sidebarToggle = document.getElementById("sidebarToggle");   
  const sidebar = document.querySelector(".admin-sidebar");   
  const content = document.querySelector(".admin-content");   
  if (sidebarToggle) {     
    sidebarToggle.addEventListener("click", () => {       
      sidebar.classList.toggle("collapsed");       
      content.classList.toggle("expanded");       
      // Save state to localStorage       
      localStorage.setItem("sidebarCollapsed", sidebar.classList.contains("collapsed"));     
    });   
  }   
  // Restore sidebar state   
  const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";   
  if (isCollapsed) {     
    sidebar.classList.add("collapsed");     
    content.classList.add("expanded");   
  }   
  // Handle submenu toggles   
  const submenuToggles = document.querySelectorAll(".submenu-toggle");   
  submenuToggles.forEach((toggle) => {     
    toggle.addEventListener("click", (e) => {       
      e.preventDefault();       
      const submenu = toggle.nextElementSibling;       
      const isOpen = submenu.classList.contains("show");       
      // Close all other submenus       
      document.querySelectorAll(".submenu.show").forEach((menu) => {         
        menu.classList.remove("show");       
      });       
      // Toggle current submenu       
      if (!isOpen) {         
        submenu.classList.add("show");       
      }     
    });   
  }); 
} 

// Enhanced data tables 
function initializeAdminDataTables() {   
  // Search functionality   
  const searchInputs = document.querySelectorAll(".table-search");   
  searchInputs.forEach((input) => {     
    let searchTimeout;     
    input.addEventListener("input", function () {       
      clearTimeout(searchTimeout);       
      const searchTerm = this.value.toLowerCase();       
      searchTimeout = setTimeout(() => {         
        filterTable(this.closest(".data-table"), searchTerm);       
      }, 300);     
    });   
  });   
  // Column sorting   
  const sortableHeaders = document.querySelectorAll(".sortable");   
  sortableHeaders.forEach((header) => {     
    header.addEventListener("click", () => {       
      sortTableByColumn(header);     
    });   
  });   
  // Row selection   
  initializeRowSelection();   
  // Pagination   
  initializePagination(); 
} 

function filterTable(tableContainer, searchTerm) {   
  const table = tableContainer.querySelector("table");   
  const rows = table.querySelectorAll("tbody tr");   
  let visibleCount = 0;   
  rows.forEach((row) => {     
    const text = row.textContent.toLowerCase();     
    const isVisible = text.includes(searchTerm);     
    row.style.display = isVisible ? "" : "none";     
    if (isVisible) visibleCount++;   
  });   
  // Update results count   
  const resultsCount = tableContainer.querySelector(".results-count");   
  if (resultsCount) {     
    resultsCount.textContent = `Menampilkan ${visibleCount} dari ${rows.length} data`;   
  }   
  // Show no results message   
  const noResults = tableContainer.querySelector(".no-results");   
  if (noResults) {     
    noResults.style.display = visibleCount === 0 ? "block" : "none";   
  } 
} 

function sortTableByColumn(header) {   
  const table = header.closest("table");   
  const tbody = table.querySelector("tbody");   
  const rows = Array.from(tbody.querySelectorAll("tr"));   
  const columnIndex = Array.from(header.parentNode.children).indexOf(header);   
  const currentSort = header.dataset.sort || "none";   
  const newSort = currentSort === "asc" ? "desc" : "asc";   
  // Clear all sort indicators   
  table.querySelectorAll(".sortable").forEach((th) => {     
    th.classList.remove("sort-asc", "sort-desc");     
    th.dataset.sort = "none";   
  });   
  // Set new sort indicator   
  header.classList.add(`sort-${newSort}`);   
  header.dataset.sort = newSort;   
  // Sort rows   
  rows.sort((a, b) => {     
    const aValue = getCellValue(a, columnIndex);     
    const bValue = getCellValue(b, columnIndex);     
    // Handle different data types     
    if (isNumeric(aValue) && isNumeric(bValue)) {       
      return newSort === "asc"         
        ? Number.parseFloat(aValue) - Number.parseFloat(bValue)         
        : Number.parseFloat(bValue) - Number.parseFloat(aValue);     
    }     
    if (isDate(aValue) && isDate(bValue)) {       
      return newSort === "asc" ? new Date(aValue) - new Date(bValue) : new Date(bValue) - new Date(aValue);     
    }     
    // String comparison     
    return newSort === "asc" ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);   
  });   
  // Reorder DOM   
  rows.forEach((row) => tbody.appendChild(row)); 
} 

function getCellValue(row, columnIndex) {   
  const cell = row.children[columnIndex];   
  return cell.dataset.value || cell.textContent.trim(); 
} 

function isNumeric(value) {   
  return !isNaN(Number.parseFloat(value)) && isFinite(value); 
} 

function isDate(value) {   
  return !isNaN(Date.parse(value)); 
} 

function initializeRowSelection() {   
  const selectAllCheckbox = document.getElementById("selectAll");   
  const rowCheckboxes = document.querySelectorAll(".row-select");   
  if (selectAllCheckbox) {     
    selectAllCheckbox.addEventListener("change", function () {       
      rowCheckboxes.forEach((checkbox) => {         
        checkbox.checked = this.checked;       
      });       
      updateBulkActionsVisibility();     
    });   
  }   
  rowCheckboxes.forEach((checkbox) => {     
    checkbox.addEventListener("change", () => {       
      updateSelectAllState();       
      updateBulkActionsVisibility();     
    });   
  }); 
} 

function updateSelectAllState() {   
  const selectAllCheckbox = document.getElementById("selectAll");   
  const rowCheckboxes = document.querySelectorAll(".row-select");   
  const checkedBoxes = document.querySelectorAll(".row-select:checked");   
  if (selectAllCheckbox) {     
    if (checkedBoxes.length === 0) {       
      selectAllCheckbox.indeterminate = false;       
      selectAllCheckbox.checked = false;     
    } else if (checkedBoxes.length === rowCheckboxes.length) {       
      selectAllCheckbox.indeterminate = false;       
      selectAllCheckbox.checked = true;     
    } else {       
      selectAllCheckbox.indeterminate = true;       
      selectAllCheckbox.checked = false;     
    }   
  } 
} 

function updateBulkActionsVisibility() {   
  const checkedBoxes = document.querySelectorAll(".row-select:checked");   
  const bulkActions = document.querySelector(".bulk-actions");   
  const selectedCount = document.querySelector(".selected-count");   
  if (bulkActions) {     
    bulkActions.style.display = checkedBoxes.length > 0 ? "block" : "none";   
  }   
  if (selectedCount) {     
    selectedCount.textContent = `${checkedBoxes.length} item dipilih`;   
  } 
} 

// Pagination 
function initializePagination() {   
  const paginationLinks = document.querySelectorAll(".pagination .page-link");   
  paginationLinks.forEach((link) => {     
    link.addEventListener("click", (e) => {       
      if (link.parentElement.classList.contains("disabled")) {         
        e.preventDefault();         
        return;       
      }       
      // Add loading state       
      link.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';     
    });   
  }); 
} 

// Modal functionality 
function initializeAdminModals() {   
  // Delete confirmation modals   
  const deleteButtons = document.querySelectorAll(".btn-delete");   
  deleteButtons.forEach((button) => {     
    button.addEventListener("click", (e) => {       
      e.preventDefault();       
      showDeleteConfirmation(button);     
    });   
  });   
  // Form modals   
  const formModals = document.querySelectorAll(".modal form");   
  formModals.forEach((form) => {     
    form.addEventListener("submit", handleModalFormSubmit);   
  }); 
} 

function showDeleteConfirmation(button) {   
  const itemName = button.dataset.itemName || "item ini";   
  const deleteUrl = button.href || button.dataset.url;   
  const modal = document.getElementById("deleteModal") || createDeleteModal();   
  const modalBody = modal.querySelector(".modal-body");   
  const confirmButton = modal.querySelector(".btn-confirm-delete");   
  modalBody.innerHTML = `     
    <div class="text-center">       
      <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>       
      <h5 class="mt-3">Konfirmasi Hapus</h5>       
      <p>Apakah Anda yakin ingin menghapus <strong>${itemName}</strong>?</p>       
      <p class="text-muted small">Tindakan ini tidak dapat dibatalkan.</p>     
    </div>   
  `;   
  confirmButton.onclick = () => {     
    window.location.href = deleteUrl;   
  };   
  const bootstrap = window.bootstrap; // Declare bootstrap variable   
  new bootstrap.Modal(modal).show(); 
} 

function createDeleteModal() {   
  const modal = document.createElement("div");   
  modal.className = "modal fade";   
  modal.id = "deleteModal";   
  modal.innerHTML = `     
    <div class="modal-dialog">       
      <div class="modal-content">         
        <div class="modal-header">           
          <h5 class="modal-title">Konfirmasi</h5>           
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>         
        </div>         
        <div class="modal-body"></div>         
        <div class="modal-footer">           
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>           
          <button type="button" class="btn btn-danger btn-confirm-delete">Hapus</button>         
        </div>       
      </div>     
    </div>   
  `;   
  document.body.appendChild(modal);   
  return modal; 
} 

function handleModalFormSubmit(e) {   
  e.preventDefault();   
  const form = e.target;   
  const submitButton = form.querySelector('button[type="submit"]');   
  const originalText = submitButton.innerHTML;   
  // Show loading state   
  submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';   
  submitButton.disabled = true;   
  // Submit form via AJAX   
  const formData = new FormData(form);   
  // Gunakan SITE_URL_JS dari window.CampusApp
  const SITE_URL_JS = window.SITE_URL || '';
  fetch(form.action, {     
    method: "POST",     
    body: formData,   
  })     
    .then((response) => response.json())     
    .then((data) => {       
      if (data.success) {         
        // Menggunakan showAlert dari window.CampusApp
        window.CampusApp.showAlert("Data berhasil disimpan", "success");         
        const bootstrap = window.bootstrap; // Declare bootstrap variable         
        bootstrap.Modal.getInstance(form.closest(".modal")).hide();         
        // Reload page or update table         
        if (data.reload) {           
          setTimeout(() => location.reload(), 1000);         
        }       
      } else {         
        // Menggunakan showAlert dari window.CampusApp
        window.CampusApp.showAlert(data.message || "Terjadi kesalahan", "danger");       
      }     
    })     
    .catch((error) => {       
      console.error("Error:", error);       
      // Menggunakan showAlert dari window.CampusApp
      window.CampusApp.showAlert("Terjadi kesalahan sistem", "danger");     
    })     
    .finally(() => {       
      // Restore button state       
      submitButton.innerHTML = originalText;       
      submitButton.disabled = false;     
    }); 
} 

// Form handling 
function initializeAdminForms() {   
  // Auto-save drafts   
  initializeAutoSave();   
  // Dynamic form fields   
  initializeDynamicFields();   
  // File upload handling   
  initializeFileUploads();   
  // Form validation   
  initializeAdvancedValidation(); 
} 

function initializeAutoSave() {   
  const autoSaveForms = document.querySelectorAll(".auto-save");   
  autoSaveForms.forEach((form) => {     
    const inputs = form.querySelectorAll("input, textarea, select");     
    inputs.forEach((input) => {       
      input.addEventListener(         
        "input",         
        window.CampusApp.debounce(() => { // Menggunakan debounce dari window.CampusApp
          saveFormDraft(form);         
        }, 2000),       
      );     
    });     
    // Load saved draft     
    loadFormDraft(form);   
  }); 
} 

function saveFormDraft(form) {   
  const formData = new FormData(form);   
  const draftData = {};   
  for (const [key, value] of formData.entries()) {     
    draftData[key] = value;   
  }   
  const draftKey = `draft_${form.id || "form"}`;   
  localStorage.setItem(draftKey, JSON.stringify(draftData));   
  window.CampusApp.showToast("Draft tersimpan", "info"); // Menggunakan showToast dari window.CampusApp
} 

function loadFormDraft(form) {   
  const draftKey = `draft_${form.id || "form"}`;   
  const savedDraft = localStorage.getItem(draftKey);   
  if (savedDraft) {     
    try {       
      const draftData = JSON.parse(savedDraft);       
      Object.keys(draftData).forEach((key) => {         
        const field = form.querySelector(`[name="${key}"]`);         
        if (field) {           
          field.value = draftData[key];         
        }       
      });       
      window.CampusApp.showToast("Draft dimuat", "info"); // Menggunakan showToast dari window.CampusApp
    } catch (error) {       
      console.error("Error loading draft:", error);     
    }   
  } 
} 

function initializeDynamicFields() {   
  // Add/remove field groups   
  const addButtons = document.querySelectorAll(".add-field");   
  const removeButtons = document.querySelectorAll(".remove-field");   
  addButtons.forEach((button) => {     
    button.addEventListener("click", () => {       
      addFieldGroup(button);     
    });   
  });   
  removeButtons.forEach((button) => {     
    button.addEventListener("click", () => {       
      removeFieldGroup(button);     
    });   
  }); 
} 

function addFieldGroup(button) {   
  const template = button.dataset.template;   
  const container = document.querySelector(button.dataset.container);   
  if (template && container) {     
    const templateElement = document.getElementById(template);     
    if (templateElement) {       
      const clone = templateElement.content.cloneNode(true);       
      // Update field names and IDs       
      const fields = clone.querySelectorAll("input, select, textarea");       
      const index = container.children.length;       
      fields.forEach((field) => {         
        if (field.name) {           
          field.name = field.name.replace("[0]", `[${index}]`);         
        }         
        if (field.id) {           
          field.id = field.id.replace("_0", `_${index}`);         
        }       
      });       
      container.appendChild(clone);     
    }   
  } 
} 

function removeFieldGroup(button) {   
  const fieldGroup = button.closest(".field-group");   
  if (fieldGroup) {     
    fieldGroup.remove();   
  } 
} 

function initializeFileUploads() {   
  const fileInputs = document.querySelectorAll(".file-upload");   
  fileInputs.forEach((input) => {     
    input.addEventListener("change", handleFileUpload);   
  });   
  // Drag and drop   
  const dropZones = document.querySelectorAll(".drop-zone");   
  dropZones.forEach((zone) => {     
    zone.addEventListener("dragover", handleDragOver);     
    zone.addEventListener("drop", handleFileDrop);   
  }); 
} 

function handleFileUpload(e) {   
  const input = e.target;   
  const files = input.files;   
  const preview = input.parentNode.querySelector(".file-preview");   
  if (files.length > 0 && preview) {     
    displayFilePreview(files[0], preview);   
  } 
} 

function displayFilePreview(file, container) {   
  container.innerHTML = "";   
  if (file.type.startsWith("image/")) {     
    const img = document.createElement("img");     
    img.src = URL.createObjectURL(file);     
    img.className = "img-thumbnail";     
    img.style.maxWidth = "200px";     
    container.appendChild(img);   
  } else {     
    const fileInfo = document.createElement("div");     
    fileInfo.className = "file-info";     
    fileInfo.innerHTML = `       
      <i class="fas fa-file"></i>       
      <span>${file.name}</span>       
      <small>(${window.CampusApp.formatFileSize(file.size)})</small>     
    `;     
    container.appendChild(fileInfo);   
  } 
} 

function handleDragOver(e) {   
  e.preventDefault();   
  e.currentTarget.classList.add("drag-over"); 
} 

function handleFileDrop(e) {   
  e.preventDefault();   
  e.currentTarget.classList.remove("drag-over");   
  const files = e.dataTransfer.files;   
  const input = e.currentTarget.querySelector("input[type='file']");   
  if (input && files.length > 0) {     
    input.files = files;     
    input.dispatchEvent(new Event("change"));   
  } 
} 

// Advanced validation 
function initializeAdvancedValidation() {   
  // Custom validation rules   
  const customValidators = {     
    nim: window.ValidationUtils.validateNIM, // Menggunakan ValidationUtils
    nidn: window.ValidationUtils.validateNIDN, // Menggunakan ValidationUtils
    email: window.ValidationUtils.validateEmail, // Menggunakan ValidationUtils
    phone: window.ValidationUtils.validatePhone, // Menggunakan ValidationUtils
    password: window.ValidationUtils.validatePassword, // Menggunakan ValidationUtils
  };   
  Object.keys(customValidators).forEach((type) => {     
    const fields = document.querySelectorAll(`[data-validate="${type}"]`);     
    fields.forEach((field) => {       
      field.addEventListener("blur", () => {         
        const isValid = customValidators[type](field.value);         
        toggleFieldValidation(field, isValid);       
      });     
    });   
  }); 
} 

// Fungsi toggleFieldValidation dipindahkan ke window.CampusApp.toggleFieldValidation
// Agar tidak ada duplikasi dan konsisten
/*
function toggleFieldValidation(field, isValid) {   
  field.classList.remove("is-valid", "is-invalid");   
  field.classList.add(isValid ? "is-valid" : "is-invalid");   
  const feedback = field.parentNode.querySelector(".invalid-feedback");   
  if (feedback) {     
    feedback.style.display = isValid ? "none" : "block";   
  } 
}
*/
function toggleFieldValidation(field, isValid, errorMessage = "") { // Membutuhkan errorMessage untuk kompatibilitas
    if (typeof window.toggleFieldValidation === 'function') { // Panggil fungsi dari validation.js jika ada
        window.toggleFieldValidation(field, isValid, errorMessage);
    } else {
        // Fallback jika tidak ada atau perlu definisi lokal
        field.classList.remove("is-valid", "is-invalid");
        field.classList.add(isValid ? "is-valid" : "is-invalid");
        const feedback = field.parentNode.querySelector(".invalid-feedback");
        if (feedback) {
            feedback.textContent = errorMessage; // Update text
            feedback.style.display = isValid ? "none" : "block";
        }
    }
}


// Dashboard widgets 
function initializeDashboardWidgets() {   
  // Animated counters   
  animateCounters();   
  // Charts   
  initializeCharts();   
  // Real-time updates   
  initializeRealTimeUpdates(); 
} 

function animateCounters() {   
  const counters = document.querySelectorAll(".counter");   
  counters.forEach((counter) => {     
    const target = Number.parseInt(counter.dataset.target);     
    const duration = Number.parseInt(counter.dataset.duration) || 2000;     
    const increment = target / (duration / 16);     
    let current = 0;     
    const updateCounter = () => {       
      current += increment;       
      if (current < target) {         
        counter.textContent = Math.floor(current);         
        requestAnimationFrame(updateCounter);       
      } else {         
        counter.textContent = target;       
      }     
    };     
    // Start animation when element is visible     
    const observer = new IntersectionObserver((entries) => {       
      entries.forEach((entry) => {         
        if (entry.isIntersecting) {           
          updateCounter();           
          observer.unobserve(entry.target);         
        }       
      });     
    });     
    observer.observe(counter);   
  }); 
} 

function initializeCharts() {   
  // Initialize Chart.js charts if library is loaded   
  if (typeof Chart !== "undefined") { // Chart diakses sebagai global
    initializeStatisticsChart();     
    initializeActivityChart();   
  } 
} 

function initializeStatisticsChart() {   
  const ctx = document.getElementById("statisticsChart");   
  if (!ctx) return;   
  new Chart(ctx, {     
    type: "line",     
    data: {       
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],       
      datasets: [         
        {           
          label: "Mahasiswa",           
          data: [12, 19, 3, 5, 2, 3],           
          borderColor: "rgb(75, 192, 192)",           
          tension: 0.1,         
        },       
      ],     
    },     
    options: {       
      responsive: true,       
      plugins: {         
        legend: {           
          position: "top",         
        },       
      },     
    },   
  }); 
} 

function initializeActivityChart() {   
  const ctx = document.getElementById("activityChart");   
  if (!ctx) return;   
  new Chart(ctx, {     
    type: "doughnut",     
    data: {       
      labels: ["Login", "Upload", "Edit", "Delete"],       
      datasets: [         
        {           
          data: [300, 50, 100, 40],           
          backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0"],         
        },       
      ],     
    },     
    options: {       
      responsive: true,     
    },   
  }); 
} 

function initializeRealTimeUpdates() {   
  // Update dashboard data every 30 seconds   
  setInterval(updateDashboardData, 30000); 
} 

function updateDashboardData() {   
  // Menggunakan SITE_URL_JS dari window.CampusApp
  const SITE_URL_JS = window.SITE_URL || '';
  fetch(`${SITE_URL_JS}/api/dashboard-stats.php`) // Asumsi ada API dashboard-stats.php
    .then((response) => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status} for ${response.url}`);
        }
        return response.json();
    })     
    .then((data) => {       
      if (data.success) {         
        updateStatCards(data.stats);       
      }     
    })     
    .catch((error) => {       
      console.error("Error updating dashboard:", error);     
    }); 
} 

function updateStatCards(stats) {   
  Object.keys(stats).forEach((key) => {     
    const element = document.querySelector(`[data-stat="${key}"]`);     
    if (element) {       
      element.textContent = stats[key];     
    }   
  }); 
} 

// Bulk actions 
function initializeBulkActions() {   
  const bulkActionSelect = document.getElementById("bulkAction");   
  const bulkActionButton = document.getElementById("bulkActionButton");   
  if (bulkActionButton) {     
    bulkActionButton.addEventListener("click", () => {       
      const action = bulkActionSelect.value;       
      const selectedIds = getSelectedRowIds();       
      if (action && selectedIds.length > 0) {         
        executeBulkAction(action, selectedIds);       
      }     
    });   
  } 
} 

function getSelectedRowIds() {   
  const checkboxes = document.querySelectorAll(".row-select:checked");   
  return Array.from(checkboxes).map((cb) => cb.value); 
} 

function executeBulkAction(action, ids) {   
  const confirmMessage = `Yakin ingin ${action} ${ids.length} item?`;   
  if (window.CampusApp.confirmDelete(confirmMessage)) { // Menggunakan confirmDelete dari window.CampusApp
    // Gunakan SITE_URL_JS dari window.CampusApp
    const SITE_URL_JS = window.SITE_URL || '';
    fetch(`${SITE_URL_JS}/api/bulk-actions.php`, { // Asumsi ada API bulk-actions.php
      method: "POST",       
      headers: {         
        "Content-Type": "application/json",       
      },       
      body: JSON.stringify({         
        action: action,         
        ids: ids,       
      }),     
    })       
      .then((response) => response.json())       
      .then((data) => {         
        if (data.success) {           
          window.CampusApp.showAlert(`${data.affected} item berhasil ${action}`, "success"); // Menggunakan showAlert dari window.CampusApp
          setTimeout(() => location.reload(), 1000);         
        } else {           
          window.CampusApp.showAlert(data.message || "Terjadi kesalahan", "danger"); // Menggunakan showAlert dari window.CampusApp
        }     
      })       
      .catch((error) => {         
        console.error("Bulk action error:", error);         
        window.CampusApp.showAlert("Terjadi kesalahan sistem", "danger"); // Menggunakan showAlert dari window.CampusApp
      });   
  } 
} 

// Utility functions (Ini adalah fungsi-fungsi yang terekspos secara global melalui window.CampusApp)
// Mendefinisikan ulang fungsi-fungsi yang di-expose via window.CampusApp di main.js
// agar admin.js bisa memanggilnya langsung atau jika main.js belum dimuat.
// Namun, jika main.js selalu dimuat sebelum admin.js, ini bisa dihapus dan langsung panggil window.CampusApp.fungsi.
// Untuk kemudahan dan menghindari duplikasi, kita akan hapus ini dan mengandalkan main.js untuk mengeksposnya.
// Pastikan tidak ada fungsi yang sama persis namanya dengan fungsi di dalam initializeApp() jika tidak ingin tumpang tindih
/*
function debounce(func, wait) {   
  let timeout;   
  return function executedFunction(...args) {     
    const later = () => {       
      clearTimeout(timeout);       
      func(...args);     
    };     
    clearTimeout(timeout);     
    timeout = setTimeout(later, wait);   
  }; 
} 

function showToast(message, type = "info") {   
  const toast = document.createElement("div");   
  toast.className = `toast align-items-center text-white bg-${type} border-0`;   
  toast.innerHTML = `     
    <div class="d-flex">       
      <div class="toast-body">${message}</div>       
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>     
    </div>   
  `;   
  const container = document.querySelector(".toast-container") || createToastContainer();   
  container.appendChild(toast);   
  const bootstrap = window.bootstrap; // Declare bootstrap variable   
  const bsToast = new bootstrap.Toast(toast);   
  bsToast.show();   
  toast.addEventListener("hidden.bs.toast", () => {     
    toast.remove();   
  }); 
} 

function createToastContainer() {   
  const container = document.createElement("div");   
  container.className = "toast-container position-fixed bottom-0 end-0 p-3";   
  document.body.appendChild(container);   
  return container; 
} 

function formatFileSize(bytes) {   
  if (bytes === 0) return "0 Bytes";   
  const k = 1024;   
  const sizes = ["Bytes", "KB", "MB", "GB"];   
  const i = Math.floor(Math.log(bytes) / Math.log(k));   
  return Number.parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]; 
} 

function showAlert(message, type = "info") {   
  const alertContainer = document.querySelector(".alert-container") || document.body;   
  const alert = document.createElement("div");   
  alert.className = `alert alert-${type} alert-dismissible fade show`;   
  alert.innerHTML = `     
    ${message}     
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>   
  `;   
  alertContainer.appendChild(alert);   
  // Auto dismiss after 5 seconds   
  setTimeout(() => {     
    if (alert.parentNode) {       
      alert.remove();     
    }   
  }, 5000); 
} 
*/
// Export functions for global use.
// Ini diasumsikan akan diinisialisasi setelah window.CampusApp dari main.js sudah ada
// atau fungsi-fungsi ini unik untuk AdminPanel.
// Melihat nama, ini adalah fungsi-fungsi utilitas, sebaiknya ditaruh di main.js atau dipanggil dari CampusApp.
// Mengubah agar AdminPanel menggunakan fungsi global dari CampusApp
window.AdminPanel = {   
  showDeleteConfirmation: showDeleteConfirmation, // Ini fungsi lokal yang unik
  showAlert: window.CampusApp.showAlert, // Menggunakan showAlert dari CampusApp
  showToast: window.CampusApp.showToast, // Menggunakan showToast dari CampusApp
  formatFileSize: window.CampusApp.formatFileSize, // Menggunakan formatFileSize dari CampusApp
  debounce: window.CampusApp.debounce, // Menggunakan debounce dari CampusApp
};
