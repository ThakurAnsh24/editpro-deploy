// EditPro Main JS - Modernized Pro Version
document.addEventListener('DOMContentLoaded', function() {
  
  // Preloader
  window.addEventListener('load', function() {
    const preloader = document.querySelector('.preloader');
    if (preloader) {
      preloader.style.opacity = '0';
      setTimeout(() => preloader.style.display = 'none', 500);
    }
  });

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^=\"#"]').forEach(anchor => {
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

  // Mobile nav toggle
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.querySelector('.nav-links');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', function() {
      navLinks.classList.toggle('active');
      navToggle.classList.toggle('active');
      document.body.classList.toggle('no-scroll');
    });
  }

  // Close mobile nav on link click
  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('active');
      navToggle.classList.remove('active');
      document.body.classList.remove('no-scroll');
    });
  });

  // Scroll progress bar
  const scrollProgress = document.getElementById('scrollProgress');
  if (scrollProgress) {
    window.addEventListener('scroll', () => {
      const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
      scrollProgress.style.width = scrolled + '%';
    });
  }

  // Custom cursor
  const customCursor = document.getElementById('customCursor');
  if (customCursor) {
    document.addEventListener('mousemove', (e) => {
      customCursor.style.left = e.clientX + 'px';
      customCursor.style.top = e.clientY + 'px';
    });
    document.querySelectorAll('a, button, .service-card').forEach(el => {
      el.addEventListener('mouseenter', () => customCursor.classList.add('hover'));
      el.addEventListener('mouseleave', () => customCursor.classList.remove('hover'));
    });
  }

  // Animate stats on scroll
  const observerOptions = {
    threshold: 0.5,
    rootMargin: '0px 0px -100px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateStats(entry.target.querySelectorAll('.stat-number'));
      }
    });
  }, observerOptions);

  // Observe hero stats
  const heroStats = document.querySelector('.hero-stats');
  if (heroStats) observer.observe(heroStats);

  function animateStats(statNumbers) {
    statNumbers.forEach(stat => {
      const target = stat.getAttribute('data-target');
      const count = animateCount(stat, target);
      stat.textContent = count;
    });
  }

  function animateCount(element, target) {
    let count = 0;
    const increment = target / 100;
    const timer = setInterval(() => {
      count += increment;
      if (count >= target) {
        element.textContent = target;
        clearInterval(timer);
      } else {
        element.textContent = Math.floor(count);
      }
    }, 20);
    return target;
  }

  // FAQ accordion
  document.querySelectorAll('.faq-item').forEach(item => {
    item.addEventListener('click', () => {
      const answer = item.querySelector('.faq-answer');
      const toggle = item.querySelector('.faq-toggle');
      
      item.classList.toggle('active');
      if (item.classList.contains('active')) {
        answer.style.maxHeight = answer.scrollHeight + 'px';
        toggle.textContent = '−';
      } else {
        answer.style.maxHeight = '0';
        toggle.textContent = '+';
      }
    });
  });

  // Form submission
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const submitBtn = this.querySelector('.submit-btn');
      const loader = submitBtn.querySelector('.loader');
      const text = submitBtn.querySelector('span');
      
      text.style.display = 'none';
      loader.style.display = 'inline-block';
      
      // Simulate submission
      setTimeout(() => {
        showToast('Quote sent! Response in 2 hours.', 'success');
        loader.style.display = 'none';
        text.style.display = 'inline';
        this.reset();
      }, 2000);
    });
  }

  // Toast notifications
  function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toastContainer.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  // Intersection Observer for fade up animations
  const fadeObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('fade-in-up');
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.service-card, .testimonial-card, .step').forEach(el => {
    fadeObserver.observe(el);
  });

  // Navbar scroll effect
  window.addEventListener('scroll', () => {
    const nav = document.querySelector('.main-nav');
    if (window.scrollY > 50) {
      nav.style.background = 'rgba(15,23,42,0.98)';
    } else {
      nav.style.background = 'rgba(15,23,42,0.95)';
    }
  });
});

// Share Site function
function shareSite() {
  const siteUrl = "https://green-tooth-a8af.itsmethakur2424.workers.dev/";
  const siteName = "Thakur.crea8tions - Professional Video Editing & Poster Design";
  const message = `Hey! Check out this site: ${siteName}\n\n${siteUrl}\n\nThey do amazing video editing and poster design! 🎬✨`;
  
  if (navigator.share) {
    // Use native share on mobile
    navigator.share({
      title: siteName,
      text: message,
      url: siteUrl
    }).catch(err => console.log('Share cancelled'));
  } else {
    // Copy to clipboard fallback
    navigator.clipboard.writeText(message).then(() => {
      showToast('Link copied! Send to your friend 📤', 'success');
    }).catch(() => {
      // Fallback: show in prompt
      prompt('Copy and send this to your friend:', message);
    });
  }
}

// Global utilities
window.showToast = showToast;
window.shareSite = shareSite;

// PWA offline support
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw.js')
    .then(reg => console.log('SW registered'))
    .catch(err => console.log('SW failed'));
}

