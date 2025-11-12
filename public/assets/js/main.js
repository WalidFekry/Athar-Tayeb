/**
 * Athar Tayeb - Main JavaScript
 * Handles theme toggle, tasbeeh counters, search, and UI interactions
 */

(function () {
  "use strict";

  // Theme Management
  const themeToggle = document.getElementById("themeToggle");
  const currentTheme = localStorage.getItem("theme") || "light";

  // Apply saved theme
  document.documentElement.setAttribute("data-theme", currentTheme);
  updateThemeIcon(currentTheme);
  updateThemeARIA(currentTheme);

  if (themeToggle) {
    themeToggle.addEventListener("click", function () {
      const theme = document.documentElement.getAttribute("data-theme");
      const newTheme = theme === "dark" ? "light" : "dark";

      document.documentElement.setAttribute("data-theme", newTheme);
      localStorage.setItem("theme", newTheme);
      updateThemeIcon(newTheme);
      updateThemeARIA(newTheme);
    });
  }

  function updateThemeIcon(theme) {
    const icon = document.querySelector(".theme-icon");
    if (icon) {
      icon.textContent = theme === "dark" ? "☀️" : "🌙";
    }
  }

  function updateThemeARIA(theme) {
    if (themeToggle) {
      themeToggle.setAttribute(
        "aria-pressed",
        theme === "dark" ? "true" : "false"
      );
      themeToggle.setAttribute(
        "aria-label",
        theme === "dark"
          ? "التبديل إلى الوضع النهاري"
          : "التبديل إلى الوضع الليلي"
      );
    }
  }

  // Tasbeeh Counter Management
  const tasbeehButtons = document.querySelectorAll(".tasbeeh-card");

  tasbeehButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const field = this.dataset.field;
      const memorialId = this.dataset.memorialId;
      const countElement = this.querySelector(".tasbeeh-count");
      const localCountElement = this.querySelector(".tasbeeh-local");

      if (!field || !memorialId) return;

      // Increment local counter immediately
      let localCount = parseInt(localCountElement?.textContent || "0");
      localCount++;
      if (localCountElement) {
        localCountElement.textContent = localCount;
      }

      // Add animation
      this.style.transform = "scale(0.95)";
      setTimeout(() => {
        this.style.transform = "";
      }, 100);

      // Send to server
      incrementTasbeeh(memorialId, field, countElement);
    });
  });

  // Local-only Tasbeeh Counters
  document.querySelectorAll(".tasbeeh-card.local-only").forEach((card) => {
    card.addEventListener("click", () => {
      const localCountSpan = card.querySelector(".tasbeeh-count");
      if (!localCountSpan) return;

      let count = parseInt(localCountSpan.textContent) || 0;
      count++;
      localCountSpan.textContent = count;

      // Optional: تأثير بصري صغير عند الضغط
      card.style.transform = "scale(0.95)";
      setTimeout(() => {
        card.style.transform = "";
      }, 100);
    });
  });

  function incrementTasbeeh(memorialId, field, countElement) {
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

    fetch(BASEURL + "/api/tasbeeh", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${memorialId}&field=${field}&csrf_token=${encodeURIComponent(
        csrfToken
      )}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.counts) {
          // Update total count
          if (countElement && data.counts[field]) {
            countElement.textContent = formatNumber(data.counts[field]);
          }
        } else if (data.error) {
          console.error("Tasbeeh error:", data.error);
        }
      })
      .catch((error) => {
        console.error("Network error:", error);
      });
  }

  function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  // Search Functionality
  const searchInput = document.getElementById("searchInput");
  const searchResults = document.getElementById("searchResults");
  let searchTimeout;

  if (searchInput && searchResults) {
    searchInput.addEventListener("input", function () {
      const query = this.value.trim();

      clearTimeout(searchTimeout);

      if (query.length < 2) {
        searchResults.innerHTML = "";
        searchResults.style.display = "none";
        return;
      }

      searchTimeout = setTimeout(() => {
        performSearch(query);
      }, 300);
    });

    // Close search results when clicking outside
    document.addEventListener("click", function (e) {
      if (
        !searchInput.contains(e.target) &&
        !searchResults.contains(e.target)
      ) {
        searchResults.style.display = "none";
      }
    });
  }

  function performSearch(query) {
    fetch(`${BASEURL}/api/search.php?q=${encodeURIComponent(query)}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.results) {
          displaySearchResults(data.results);
        } else {
          searchResults.innerHTML =
            '<div class="search-result-item">لا توجد نتائج</div>';
          searchResults.style.display = "block";
        }
      })
      .catch((error) => {
        console.error("Search error:", error);
        searchResults.innerHTML =
          '<div class="search-result-item">حدث خطأ في البحث</div>';
        searchResults.style.display = "block";
      });
  }

  function displaySearchResults(results) {
    if (results.length === 0) {
      searchResults.innerHTML =
        '<div class="search-result-item">لا توجد نتائج</div>';
      searchResults.style.display = "block";
      return;
    }

    let html = "";
    results.forEach((result) => {
      const url = `${BASEURL}/m/${result.id}`;
      html += `
                <a href="${url}" class="search-result-item text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="fw-bold">${escapeHtml(
                              result.name
                            )}</div>
                            ${
                              result.death_date
                                ? `<small class="text-muted">${result.death_date}</small>`
                                : ""
                            }
                        </div>
                    </div>
                </a>
            `;
    });

    searchResults.innerHTML = html;
    searchResults.style.display = "block";
  }

  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // Image Preview on Upload
  const imageInput = document.getElementById("imageInput");
  const imagePreview = document.getElementById("imagePreview");

  if (imageInput && imagePreview) {
    imageInput.addEventListener("change", function (e) {
      const file = e.target.files[0];

      if (file) {
        // Validate file size
        if (file.size > 2 * 1024 * 1024) {
          alert("حجم الملف كبير جداً (الحد الأقصى 2 ميجابايت)");
          this.value = "";
          imagePreview.innerHTML = "";
          return;
        }

        // Validate file type
        if (!["image/jpeg", "image/png"].includes(file.type)) {
          alert("نوع الملف غير مسموح (فقط JPG و PNG)");
          this.value = "";
          imagePreview.innerHTML = "";
          return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function (e) {
          imagePreview.innerHTML = `
                        <img src="${e.target.result}" alt="معاينة الصورة" class="memorial-image">
                    `;
        };
        reader.readAsDataURL(file);
      } else {
        imagePreview.innerHTML = "";
      }
    });
  }

  // Copy to Clipboard
  const copyButtons = document.querySelectorAll(".copy-link-btn");

  copyButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const url = this.dataset.url;

      if (navigator.clipboard) {
        navigator.clipboard
          .writeText(url)
          .then(() => {
            showNotification("تم نسخ الرابط بنجاح ✓");
          })
          .catch((err) => {
            fallbackCopy(url);
          });
      } else {
        fallbackCopy(url);
      }
    });
  });

  function fallbackCopy(text) {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    textarea.style.opacity = "0";
    document.body.appendChild(textarea);
    textarea.select();

    try {
      document.execCommand("copy");
      showNotification("تم نسخ الرابط بنجاح ✓");
    } catch (err) {
      showNotification("فشل نسخ الرابط");
    }

    document.body.removeChild(textarea);
  }

  function showNotification(message) {
    const notification = document.createElement("div");
    notification.className =
      "alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3";
    notification.style.zIndex = "9999";
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.remove();
    }, 3000);
  }

  // Asma Allah "Show More" functionality
  const showMoreBtn = document.getElementById("showMoreAsma");
  const hiddenAsma = document.querySelectorAll(".asma-item.hidden");

  if (showMoreBtn && hiddenAsma.length > 0) {
    showMoreBtn.addEventListener("click", function () {
      hiddenAsma.forEach((item) => {
        item.classList.remove("hidden");
        item.style.display = "block";
      });
      this.style.display = "none";
    });
  }

  // Audio Player Management (ensure only one plays at a time)
  const audioPlayers = document.querySelectorAll("audio");

  audioPlayers.forEach((player) => {
    player.addEventListener("play", function () {
      audioPlayers.forEach((otherPlayer) => {
        if (otherPlayer !== player) {
          otherPlayer.pause();
        }
      });
    });
  });

  // Form Validation Enhancement
  const forms = document.querySelectorAll("form[data-validate]");

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  });

  // Smooth Scroll for Anchor Links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href");
      if (href !== "#" && href !== "#!") {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      }
    });
  });

  // Initialize tooltips if Bootstrap is available
  if (typeof bootstrap !== "undefined" && bootstrap.Tooltip) {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  // Back to Top Button
  const backToTopBtn = document.getElementById("backToTop");

  if (backToTopBtn) {
    // Show/hide button on scroll
    window.addEventListener("scroll", function () {
      if (window.pageYOffset > 300) {
        backToTopBtn.classList.add("show");
      } else {
        backToTopBtn.classList.remove("show");
      }
    });

    // Scroll to top on click
    backToTopBtn.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }
})();

document.addEventListener("DOMContentLoaded", function () {
  // Surah Modals - Check if Bootstrap is available
  if (typeof bootstrap === "undefined") {
    console.warn("Bootstrap is not loaded. Modal functionality will not work.");
    return;
  }

  // Get modal elements
  const yaseenModal = document.getElementById("yaseenModal");
  const fatihaModal = document.getElementById("fatihaModal");

  // Read Yaseen button (on main page)
  const readYaseenBtn = document.getElementById("readYaseenBtn");
  if (readYaseenBtn && yaseenModal) {
    readYaseenBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const modal = new bootstrap.Modal(yaseenModal);
      modal.show();
    });
  }

  // Read Fatiha button (on main page - direct access)
  const readFatihaDirectBtn = document.getElementById("readFatihaDirectBtn");
  if (readFatihaDirectBtn && fatihaModal) {
    readFatihaDirectBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const modal = new bootstrap.Modal(fatihaModal);
      modal.show();
    });
  }

  // Read Fatiha button (inside Yaseen modal) - Use event delegation
  if (yaseenModal && fatihaModal) {
    yaseenModal.addEventListener("click", function (e) {
      // Check if the clicked element is the readFatihaBtn
      if (e.target && e.target.id === "readFatihaBtn") {
        e.preventDefault();

        // Close Yaseen modal
        const yaseenModalInstance = bootstrap.Modal.getInstance(yaseenModal);
        if (yaseenModalInstance) {
          yaseenModalInstance.hide();
        }

        // Open Fatiha modal after a short delay to allow Yaseen to close
        setTimeout(() => {
          const fatihaModalInstance = new bootstrap.Modal(fatihaModal);
          fatihaModalInstance.show();
        }, 300);
      }
    });
  }

  // Azkar Modal Functionality
  const azkarButtons = document.querySelectorAll(".azkar-read-btn");
  const azkarModalElement = document.getElementById("azkarModal");
  const azkarModalImage = document.getElementById("azkarModalImage");
  const azkarModalLabel = document.getElementById("azkarModalLabel");

  if (azkarButtons.length > 0 && azkarModalElement) {
    const azkarModal = new bootstrap.Modal(azkarModalElement);

    azkarButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const imageUrl = this.getAttribute("data-azkar-image");
        const title = this.getAttribute("data-azkar-title");

        azkarModalImage.src = imageUrl;
        azkarModalLabel.textContent = title;
        azkarModal.show();
      });
    });

    // Zoom functionality
    azkarModalImage.addEventListener("click", function () {
      this.classList.toggle("zoomed");
    });

    // Reset zoom when modal closes
    azkarModalElement.addEventListener("hidden.bs.modal", function () {
      azkarModalImage.classList.remove("zoomed");
    });
  }

  // Quran Radio Functionality
  const quranRadio = document.getElementById("quranRadio");
  const playBtn = document.getElementById("playRadioBtn");
  const pauseBtn = document.getElementById("pauseRadioBtn");
  const volumeControl = document.getElementById("radioVolume");

  if (quranRadio && playBtn && pauseBtn && volumeControl) {
    // Set initial volume
    quranRadio.volume = volumeControl.value / 100;

    playBtn.addEventListener("click", function () {
      quranRadio.play();
      playBtn.style.display = "none";
      pauseBtn.style.display = "inline-block";
    });

    pauseBtn.addEventListener("click", function () {
      quranRadio.pause();
      pauseBtn.style.display = "none";
      playBtn.style.display = "inline-block";
    });

    volumeControl.addEventListener("input", function () {
      quranRadio.volume = this.value / 100;
    });
  }

  // Ruqyah Single Audio Player with Random Track
  const roqiaPlayBtn = document.querySelector(".ruqyah-play-btn");
  const audioElement = document.getElementById("ruqyahAudio");
  const playIcon = roqiaPlayBtn.querySelector(".play-icon");
  const pauseIcon = roqiaPlayBtn.querySelector(".pause-icon");

  let currentTrack = null;
  const tracks = [
    "https://post.walid-fekry.com/audios/roqia/1.mp3",
    "https://post.walid-fekry.com/audios/roqia/2.mp3",
    "https://post.walid-fekry.com/audios/roqia/3.mp3",
    "https://post.walid-fekry.com/audios/roqia/4.mp3",
    "https://post.walid-fekry.com/audios/roqia/5.mp3",
  ];

  function getRandomTrack() {
    const index = Math.floor(Math.random() * tracks.length);
    return tracks[index];
  }

  roqiaPlayBtn.addEventListener("click", () => {
    if (!audioElement.paused) {
      // pause
      audioElement.pause();
      playIcon.style.display = "inline";
      pauseIcon.style.display = "none";
      roqiaPlayBtn.classList.remove("playing");
    } else {
      // choose random if not playing or ended
      if (!currentTrack || audioElement.ended) {
        const randomSrc = getRandomTrack();
        currentTrack = randomSrc;
        audioElement.src = randomSrc;
      }
      audioElement.play();
      playIcon.style.display = "none";
      pauseIcon.style.display = "inline";
      roqiaPlayBtn.classList.add("playing");
    }
  });

  audioElement.addEventListener("ended", () => {
    playIcon.style.display = "inline";
    pauseIcon.style.display = "none";
    roqiaPlayBtn.classList.remove("playing");
  });
});
