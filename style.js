        // Dynamic image slider for hero section
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.hero-slide');
            let currentSlide = 0;

            function nextSlide() {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }

            // Change slide every 5 seconds
            setInterval(nextSlide, 5000);
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Counter animation for stats
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-card h3');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/\D/g, ''));
                const suffix = counter.textContent.replace(/[0-9]/g, '');
                let current = 0;
                const increment = target / 50;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    counter.textContent = Math.floor(current) + suffix;
                }, 50);
            });
        }

        // Trigger counter animation when stats section comes into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });

        observer.observe(document.querySelector('.stats-section'));
		
		const popup = document.getElementById("adPopup");
  const closeBtn = document.getElementById("closeAd");

  // Close popup on click of X
  closeBtn.onclick = function () {
    popup.style.display = "none";
  };

  // Close popup on outside click
  window.onclick = function(e) {
    if (e.target === popup) {
      popup.style.display = "none";
    }
  };

  // Hover effect for close button
  closeBtn.onmouseover = function () {
    closeBtn.style.color = "red";
  };
  closeBtn.onmouseout = function () {
    closeBtn.style.color = "#444";
  };