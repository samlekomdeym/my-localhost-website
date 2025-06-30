// Form validation functions and utilities 
class FormValidator {   
  constructor(form, options = {}) {     
    this.form = form;     
    this.options = {       
      validateOnBlur: true,       
      validateOnInput: false,       
      showErrors: true,       
      errorClass: "is-invalid",       
      successClass: "is-valid",       
      ...options,     
    };     
    this.rules = {};     
    this.messages = {};     
    this.errors = {};     
    this.init();   
  }   
  init() {     
    this.setupEventListeners();     
    this.loadRulesFromAttributes();   
  }   
  setupEventListeners() {     
    if (this.options.validateOnBlur) {       
      this.form.addEventListener("blur", this.handleBlur.bind(this), true);     
    }     
    if (this.options.validateOnInput) {       
      this.form.addEventListener("input", this.handleInput.bind(this), true);     
    }     
    this.form.addEventListener("submit", this.handleSubmit.bind(this));   
  }   
  loadRulesFromAttributes() {     
    const fields = this.form.querySelectorAll("[data-validate]");     
    fields.forEach((field) => {       
      const rules = field.dataset.validate.split("|");       
      const fieldName = field.name || field.id;       
      this.rules[fieldName] = rules;       
      // Load custom message if exists       
      if (field.dataset.message) {         
        this.messages[fieldName] = field.dataset.message;       
      }     
    });   
  }   
  handleBlur(e) {     
    if (this.isValidatableField(e.target)) {       
      this.validateField(e.target);     
    }   
  }   
  handleInput(e) {     
    if (this.isValidatableField(e.target) && e.target.classList.contains(this.options.errorClass)) {       
      this.validateField(e.target);     
    }   
  }   
  handleSubmit(e) {     
    if (!this.validateForm()) {       
      e.preventDefault();       
      e.stopPropagation();       
      // Focus on first error field       
      const firstError = this.form.querySelector(`.${this.options.errorClass}`);       
      if (firstError) {         
        firstError.focus();       
      }     
    }   
  }   
  isValidatableField(field) {     
    return field.name && (field.dataset.validate || field.hasAttribute("required"));   
  }   
  validateField(field) {     
    const fieldName = field.name || field.id;     
    const value = this.getFieldValue(field);     
    const rules = this.rules[fieldName] || [];     
    // Add required rule if field has required attribute     
    if (field.hasAttribute("required") && !rules.includes("required")) {       
      rules.unshift("required");     
    }     
    let isValid = true;     
    let errorMessage = "";     
    for (const rule of rules) {       
      const result = this.applyRule(rule, value, field);       
      if (!result.valid) {         
        isValid = false;         
        errorMessage = result.message;         
        break;       
      }     
    }     
    this.updateFieldUI(field, isValid, errorMessage);     
    if (isValid) {       
      delete this.errors[fieldName];     
    } else {       
      this.errors[fieldName] = errorMessage;     
    }     
    return isValid;   
  }   
  validateForm() {     
    const fields = this.form.querySelectorAll("input, select, textarea");     
    let isValid = true;     
    this.errors = {};     
    fields.forEach((field) => {       
      if (this.isValidatableField(field)) {         
        if (!this.validateField(field)) {           
          isValid = false;         
        }       
      }     
    });     
    return isValid;   
  }   
  getFieldValue(field) {     
    if (field.type === "checkbox") {       
      return field.checked;     
    } else if (field.type === "radio") {       
      const checked = this.form.querySelector(`input[name="${field.name}"]:checked`);       
      return checked ? checked.value : "";     
    } else if (field.type === "file") {       
      return field.files;     
    } else {       
      return field.value.trim();     
    }   
  }   
  applyRule(rule, value, field) {     
    const [ruleName, ...params] = rule.split(":");     
    const param = params.join(":");     
    switch (ruleName) {       
      case "required":         
        return this.validateRequired(value, field);       
      case "email":         
        return this.validateEmail(value);       
      case "min":         
        return this.validateMin(value, Number.parseInt(param));       
      case "max":         
        return this.validateMax(value, Number.parseInt(param));       
      case "minlength":         
        return this.validateMinLength(value, Number.parseInt(param));       
      case "maxlength":         
        return this.validateMaxLength(value, Number.parseInt(param));       
      case "pattern":         
        return this.validatePattern(value, param);       
      case "numeric":         
        return this.validateNumeric(value);       
      case "alpha":         
        return this.validateAlpha(value);       
      case "alphanumeric":         
        return this.validateAlphanumeric(value);       
      case "phone":         
        return this.validatePhone(value);       
      case "url":         
        return this.validateUrl(value);       
      case "date":         
        return this.validateDate(value);       
      case "confirmed":         
        return this.validateConfirmed(value, param, field);       
      case "nim":         
        return this.validateNIM(value);       
      case "nidn":         
        return this.validateNIDN(value);       
      case "password":         
        return this.validatePassword(value);       
      case "file":         
        return this.validateFile(value, param);       
      default:         
        return { valid: true, message: "" };     
    }   
  }   
  validateRequired(value, field) {     
    let isEmpty = false;     
    if (field.type === "checkbox" || field.type === "radio") {       
      isEmpty = !value;     
    } else if (field.type === "file") {       
      isEmpty = !value || value.length === 0;     
    } else {       
      isEmpty = !value || value.length === 0;     
    }     
    return {       
      valid: !isEmpty,       
      message: "Field ini wajib diisi",     
    };   
  }   
  validateEmail(value) {     
    if (!value) return { valid: true, message: "" };     
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;     
    return {       
      valid: emailRegex.test(value),       
      message: "Format email tidak valid",     
    };   
  }   
  validateMin(value, min) {     
    if (!value) return { valid: true, message: "" };     
    const numValue = Number.parseFloat(value);     
    return {       
      valid: !isNaN(numValue) && numValue >= min,       
      message: `Nilai minimal adalah ${min}`,     
    };   
  }   
  validateMax(value, max) {     
    if (!value) return { valid: true, message: "" };     
    const numValue = Number.parseFloat(value);     
    return {       
      valid: !isNaN(numValue) && numValue <= max,       
      message: `Nilai maksimal adalah ${max}`,     
    };   
  }   
  validateMinLength(value, minLength) {     
    if (!value) return { valid: true, message: "" };     
    return {       
      valid: value.length >= minLength,       
      message: `Minimal ${minLength} karakter`,     
    };   
  }   
  validateMaxLength(value, maxLength) {     
    if (!value) return { valid: true, message: "" };     
    return {       
      valid: value.length <= maxLength,       
      message: `Maksimal ${maxLength} karakter`,     
    };   
  }   
  validatePattern(value, pattern) {     
    if (!value) return { valid: true, message: "" };     
    const regex = new RegExp(pattern);     
    return {       
      valid: regex.test(value),       
      message: "Format tidak sesuai",     
    };   
  }   
  validateNumeric(value) {     
    if (!value) return { valid: true, message: "" };     
    return {       
      valid: /^\d+(\.\d+)?$/.test(value),       
      message: "Hanya boleh berisi angka",     
    };   
  }   
  validateAlpha(value) {     
    if (!value) return { valid: true, message: "" };     
    return {       
      valid: /^[a-zA-Z\s]+$/.test(value),       
      message: "Hanya boleh berisi huruf",     
    };   
  }   
  validateAlphanumeric(value) {     
    if (!value) return { valid: true, message: "" };     
    return {       
      valid: /^[a-zA-Z0-9\s]+$/.test(value),       
      message: "Hanya boleh berisi huruf dan angka",     
    };   
  }   
  validatePhone(value) {     
    if (!value) return { valid: true, message: "" };     
    // Regex lebih longgar untuk nomor telepon Indonesia
    const phoneRegex = /^(?:\+62|0)[2-9]\d{7,11}$/; 
    return {       
      valid: phoneRegex.test(value),       
      message: "Format nomor telepon tidak valid (misal: 081234567890 atau +6281234567890)",     
    };   
  }   
  validateUrl(value) {     
    if (!value) return { valid: true, message: "" };     
    try {       
      new URL(value);       
      return { valid: true, message: "" };     
    } catch (e) { // Tangkap error jika URL tidak valid
      return { valid: false, message: "Format URL tidak valid" };     
    }   
  }   
  validateDate(value) {     
    if (!value) return { valid: true, message: "" };     
    const date = new Date(value);     
    return {       
      valid: !isNaN(date.getTime()),       
      message: "Format tanggal tidak valid",     
    };   
  }   
  validateConfirmed(value, confirmFieldName, field) {     
    if (!value) return { valid: true, message: "" };     
    const confirmField = this.form.querySelector(`[name="${confirmFieldName}"]`);     
    const confirmValue = confirmField ? confirmField.value : "";     
    return {       
      valid: value === confirmValue,       
      message: "Konfirmasi tidak cocok",     
    };   
  }   
  validateNIM(value) {     
    if (!value) return { valid: true, message: "" };     
    // NIM format: 10 digits (sesuai contoh 20240001)
    return {       
      valid: /^\d{10}$/.test(value),       
      message: "NIM harus 10 digit angka",     
    };   
  }   
  validateNIDN(value) {     
    if (!value) return { valid: true, message: "" };     
    // NIDN format: 10 digits (sesuai contoh 0101010001)
    return {       
      valid: /^\d{10}$/.test(value),       
      message: "NIDN harus 10 digit angka",     
    };   
  }   
  validatePassword(value) {     
    if (!value) return { valid: true, message: "" };     
    // At least 6 characters, tidak ada kewajiban huruf besar/kecil/angka khusus di sini
    const passwordRegex = /^.{6,}$/; 
    return {       
      valid: passwordRegex.test(value),       
      message: "Password minimal 6 karakter",     
    };   
  }   
  validateFile(files, allowedTypes) {     
    if (!files || files.length === 0) return { valid: true, message: "" };     
    const file = files[0];     
    const maxSize = 5 * 1024 * 1024; // 5MB     
    // Check file size     
    if (file.size > maxSize) {       
      return {         
        valid: false,         
        message: "Ukuran file maksimal 5MB",       
      };     
    }     
    // Check file type     
    if (allowedTypes) {       
      const types = allowedTypes.split(",").map((type) => type.trim());       
      const fileExtension = file.name.split(".").pop().toLowerCase();       
      if (!types.includes(fileExtension)) {         
        return {           
          valid: false,           
          message: `Tipe file yang diizinkan: ${types.join(", ")}`,         
        };       
      }     
    }     
    return { valid: true, message: "" };   
  }   
  updateFieldUI(field, isValid, errorMessage) {     
    if (!this.options.showErrors) return;     
    // Remove existing classes     
    field.classList.remove(this.options.errorClass, this.options.successClass);     
    // Add appropriate class     
    field.classList.add(isValid ? this.options.successClass : this.options.errorClass);     
    // Update error message     
    this.updateErrorMessage(field, isValid ? "" : errorMessage);   
  }   
  updateErrorMessage(field, message) {     
    const fieldContainer = field.closest(".form-group") || field.parentNode;     
    let errorElement = fieldContainer.querySelector(".invalid-feedback");     
    if (!errorElement) {       
      errorElement = document.createElement("div");       
      errorElement.className = "invalid-feedback";       
      fieldContainer.appendChild(errorElement);     
    }     
    errorElement.textContent = message;     
    errorElement.style.display = message ? "block" : "none";   
  }   
  // Public methods   
  addRule(fieldName, rule) {     
    if (!this.rules[fieldName]) {       
      this.rules[fieldName] = [];     
    }     
    this.rules[fieldName].push(rule);   
  }   
  removeRule(fieldName, rule) {     
    if (this.rules[fieldName]) {       
      this.rules[fieldName] = this.rules[fieldName].filter((r) => r !== rule);     
    }   
  }   
  setMessage(fieldName, message) {     
    this.messages[fieldName] = message;   
  }   
  getErrors() {     
    return this.errors;   
  }   
  hasErrors() {     
    return Object.keys(this.errors).length > 0;   
  }   
  clearErrors() {     
    this.errors = {};     
    // Clear UI     
    const fields = this.form.querySelectorAll(`.${this.options.errorClass}`);     
    fields.forEach((field) => {       
      field.classList.remove(this.options.errorClass, this.options.successClass);       
      this.updateErrorMessage(field, "");     
    });   
  }   
  reset() {     
    this.clearErrors();     
    this.form.reset();   
  } 
} 

// Auto-initialize validation for forms with .needs-validation class 
document.addEventListener("DOMContentLoaded", () => {   
  const forms = document.querySelectorAll(".needs-validation");   
  forms.forEach((form) => {     
    new FormValidator(form);   
  }); 
}); 

// Utility functions for standalone validation (Diekspos di window.ValidationUtils)
const ValidationUtils = {   
  validateEmail(email) {     
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);   
  },   
  validatePhone(phone) {     
    // Regex lebih longgar untuk nomor telepon Indonesia
    return /^(?:\+62|0)[2-9]\d{7,11}$/.test(phone);   
  },   
  validatePassword(password) {     
    // Password minimal 6 karakter
    return /^.{6,}$/.test(password);   
  },   
  validateNIM(nim) {     
    return /^\d{10}$/.test(nim);   
  },   
  validateNIDN(nidn) {     
    return /^\d{10}$/.test(nidn);   
  },   
  validateRequired(value) {     
    return value !== null && value !== undefined && value.toString().trim() !== "";   
  },   
  validateNumeric(value) {     
    return /^\d+(\.\d+)?$/.test(value);   
  },   
  validateAlpha(value) {     
    return /^[a-zA-Z\s]+$/.test(value);   
  },   
  validateAlphanumeric(value) {     
    return /^[a-zA-Z0-9\s]+$/.test(value);   
  },   
  validateUrl(url) {     
    try {       
      new URL(url);       
      return true;     
    } catch (e) {       
      return false;     
    }   
  },   
  validateDate(date) {     
    return !isNaN(new Date(date).getTime());   
  },   
  validateFileSize(file, maxSizeMB = 5) {     
    return file.size <= maxSizeMB * 1024 * 1024;   
  },   
  validateFileType(file, allowedTypes) {     
    const extension = file.name.split(".").pop().toLowerCase();     
    return allowedTypes.includes(extension);   
  }, 
}; 

// Real-time validation helpers (Diekspos di window)
// Perbaikan: Pastikan field.parentNode.querySelector('.invalid-feedback') menemukan elemen yang benar
function addRealTimeValidation(field, validator, errorMessage) {   
  field.addEventListener("blur", () => {     
    const isValid = validator(field.value);     
    toggleFieldValidation(field, isValid, errorMessage);   
  });   
  field.addEventListener("input", () => {     
    if (field.classList.contains("is-invalid")) {       
      const isValid = validator(field.value);       
      toggleFieldValidation(field, isValid, errorMessage);     
    }   
  }); 
} 

function toggleFieldValidation(field, isValid, errorMessage = "") {   
  field.classList.remove("is-valid", "is-invalid");   
  field.classList.add(isValid ? "is-valid" : "is-invalid");   
  const fieldContainer = field.closest(".form-group") || field.parentNode;   
  let errorElement = fieldContainer.querySelector(".invalid-feedback");   
  if (!errorElement && !isValid) {     
    errorElement = document.createElement("div");     
    errorElement.className = "invalid-feedback";     
    fieldContainer.appendChild(errorElement);   
  }   
  if (errorElement) {     
    errorElement.textContent = isValid ? "" : errorMessage;     
    errorElement.style.display = isValid ? "none" : "block";   
  } 
} 

// Password strength indicator 
function addPasswordStrengthIndicator(passwordField) {   
  const container = passwordField.closest(".form-group") || passwordField.parentNode;   
  const strengthIndicator = document.createElement("div");   
  strengthIndicator.className = "password-strength mt-2";   
  strengthIndicator.innerHTML = `     
    <div class="strength-bar">       
      <div class="strength-fill"></div>     
    </div>     
    <div class="strength-text">Kekuatan password</div>   
  `;   
  container.appendChild(strengthIndicator);   
  passwordField.addEventListener("input", () => {     
    const strength = calculatePasswordStrength(passwordField.value);     
    updatePasswordStrengthUI(strengthIndicator, strength);   
  }); 
} 

function calculatePasswordStrength(password) {   
  let score = 0;   
  const feedback = [];   
  // Menyesuaikan logika strength agar sesuai dengan password minimal 6 karakter
  if (password.length >= 6) score += 1;
  else feedback.push("Minimal 6 karakter");

  if (/[a-z]/.test(password)) score += 1;
  else feedback.push("Huruf kecil");
  
  if (/[A-Z]/.test(password)) score += 1;
  else feedback.push("Huruf besar");
  
  if (/\d/.test(password)) score += 1;
  else feedback.push("Angka");
  
  // Mengurangi total score maksimal jika kriteria lebih sedikit
  const maxScore = 5; // Asumsi tetap 5 jika karakter khusus tetap dihitung
  if (/[^a-zA-Z\d]/.test(password)) score += 1;
  else feedback.push("Karakter khusus");
  
  const levels = ["Sangat Lemah", "Lemah", "Sedang", "Kuat", "Sangat Kuat"];   
  const colors = ["#dc3545", "#fd7e14", "#ffc107", "#28a745", "#20c997"];   
  
  return {     
    score,     
    level: levels[Math.min(score, levels.length -1)] || levels[0], // Pastikan indeks tidak melebihi batas array
    color: colors[Math.min(score, colors.length -1)] || colors[0], 
    feedback: feedback,   
  }; 
} 

function updatePasswordStrengthUI(indicator, strength) {   
  const fill = indicator.querySelector(".strength-fill");   
  const text = indicator.querySelector(".strength-text");   
  const percentage = (strength.score / 5) * 100; // Pembagian disesuaikan dengan maxScore
  fill.style.width = `${percentage}%`;   
  fill.style.backgroundColor = strength.color;   
  text.textContent = `${strength.level}`;   
  if (strength.feedback.length > 0) {     
    text.textContent += ` (Perlu: ${strength.feedback.join(", ")})`;   
  } 
} 

// Export for global use 
window.FormValidator = FormValidator; 
window.ValidationUtils = ValidationUtils; 
window.addRealTimeValidation = addRealTimeValidation; 
window.toggleFieldValidation = toggleFieldValidation; 
window.addPasswordStrengthIndicator = addPasswordStrengthIndicator;
