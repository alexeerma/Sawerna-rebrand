(function () {
  const sliders = document.querySelectorAll('[data-home-slider]');

  sliders.forEach((slider) => {
    const track = slider.querySelector('[data-home-slider-track="1"]');
    const dots = Array.from(slider.querySelectorAll('[data-home-slider-dot]'));
    const prev = slider.querySelector('[data-home-slider-prev="1"]');
    const next = slider.querySelector('[data-home-slider-next="1"]');
    const slideCount = dots.length || track?.children.length || 0;
    let activeIndex = 0;
    let autoplayTimer = null;

    const goToSlide = (index) => {
      if (!track || slideCount < 1) {
        return;
      }

      activeIndex = (index + slideCount) % slideCount;
      track.style.transform = `translateX(-${activeIndex * 100}%)`;

      dots.forEach((dot, dotIndex) => {
        const isActive = dotIndex === activeIndex;
        dot.classList.toggle('is-active', isActive);
        dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    };

    const restartAutoplay = () => {
      if (autoplayTimer) {
        window.clearInterval(autoplayTimer);
      }

      if (slideCount < 2) {
        return;
      }

      autoplayTimer = window.setInterval(() => {
        goToSlide(activeIndex + 1);
      }, 6000);
    };

    dots.forEach((dot) => {
      dot.addEventListener('click', () => {
        goToSlide(Number(dot.getAttribute('data-home-slider-dot')) || 0);
        restartAutoplay();
      });
    });

    if (prev) {
      prev.addEventListener('click', () => {
        goToSlide(activeIndex - 1);
        restartAutoplay();
      });
    }

    if (next) {
      next.addEventListener('click', () => {
        goToSlide(activeIndex + 1);
        restartAutoplay();
      });
    }

    goToSlide(0);
    restartAutoplay();
  });

  const categoryBlocks = document.querySelectorAll('[data-home-categories="1"]');

  categoryBlocks.forEach((block) => {
    const toggle = block.querySelector('[data-home-categories-toggle="1"]');
    const toggleLabel = block.querySelector('[data-home-categories-toggle-label="1"]');
    const more = block.querySelector('[data-home-categories-more="1"]');

    if (!toggle || !toggleLabel || !more) {
      return;
    }

    const setExpanded = (expanded) => {
      block.classList.toggle('is-expanded', expanded);
      toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      toggleLabel.textContent = expanded
        ? toggle.getAttribute('data-label-less') || ''
        : toggle.getAttribute('data-label-more') || '';

      if (expanded) {
        more.hidden = false;
        more.style.maxHeight = '0px';
        // Double RAF: first frame paints the element at max-height:0,
        // second frame starts the transition to full height.
        window.requestAnimationFrame(() => {
          window.requestAnimationFrame(() => {
            more.style.maxHeight = `${more.scrollHeight}px`;
          });
        });
      } else {
        more.style.maxHeight = `${more.scrollHeight}px`;
        window.requestAnimationFrame(() => {
          window.requestAnimationFrame(() => {
            more.style.maxHeight = '0px';
          });
        });
        window.setTimeout(() => {
          if (!block.classList.contains('is-expanded')) {
            more.hidden = true;
            more.style.maxHeight = '';
          }
        }, 560);
      }
    };

    toggle.addEventListener('click', () => {
      const expanded = block.classList.contains('is-expanded');
      setExpanded(!expanded);
    });
  });

  const productSliders = document.querySelectorAll('[data-home-products-slider="1"]');

  productSliders.forEach((slider) => {
    const viewport = slider.querySelector('.sawerna-home-products__viewport');
    const track = slider.querySelector('[data-home-products-track="1"]');
    const nav = slider.querySelector('[data-home-products-nav="1"]');
    const prev = slider.querySelector('[data-home-products-prev="1"]');
    const next = slider.querySelector('[data-home-products-next="1"]');
    const items = track ? Array.from(track.children) : [];

    if (!viewport || !track || items.length < 2) {
      return;
    }

    let index = 0;

    const getStep = () => {
      const gap = parseFloat(window.getComputedStyle(track).columnGap) || 0;
      return items[0].getBoundingClientRect().width + gap;
    };

    const getMaxIndex = () => {
      const maxScroll = track.scrollWidth - viewport.clientWidth;
      const step = getStep();

      if (maxScroll <= 1 || !step) {
        return 0;
      }

      return Math.ceil(maxScroll / step);
    };

    const update = () => {
      const maxIndex = getMaxIndex();

      if (maxIndex < 1) {
        if (nav) {
          nav.hidden = true;
        }

        track.style.transform = 'translateX(0)';
        index = 0;
        return;
      }

      if (nav) {
        nav.hidden = false;
      }

      index = Math.min(index, maxIndex);

      const maxScroll = track.scrollWidth - viewport.clientWidth;
      const offset = Math.min(index * getStep(), maxScroll);
      track.style.transform = `translateX(-${offset}px)`;

      if (prev) {
        prev.disabled = index <= 0;
      }

      if (next) {
        next.disabled = index >= maxIndex;
      }
    };

    if (prev) {
      prev.addEventListener('click', () => {
        index -= 1;
        update();
      });
    }

    if (next) {
      next.addEventListener('click', () => {
        index += 1;
        update();
      });
    }

    window.addEventListener('resize', update);
    update();

    slider.sawernaProductsSliderUpdate = update;
  });

  const recommendationBlocks = document.querySelectorAll('[data-home-recommendations="1"]');

  recommendationBlocks.forEach((block) => {
    const tabs = Array.from(block.querySelectorAll('[data-home-recommendations-tab]'));
    const panels = Array.from(block.querySelectorAll('[data-home-recommendations-panel]'));

    if (!tabs.length || !panels.length) {
      return;
    }

    const activateTab = (index) => {
      tabs.forEach((tab, tabIndex) => {
        const isActive = tabIndex === index;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });

      panels.forEach((panel, panelIndex) => {
        const isActive = panelIndex === index;
        panel.classList.toggle('is-active', isActive);
        panel.hidden = !isActive;

        if (isActive) {
          panel.querySelectorAll('[data-home-products-slider="1"]').forEach((panelSlider) => {
            if (typeof panelSlider.sawernaProductsSliderUpdate === 'function') {
              panelSlider.sawernaProductsSliderUpdate();
            }
          });
        }
      });
    };

    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        activateTab(Number(tab.getAttribute('data-home-recommendations-tab')) || 0);
      });
    });
  });

  const testimonialBlocks = document.querySelectorAll('[data-home-testimonials="1"]');

  testimonialBlocks.forEach((block) => {
    const track = block.querySelector('[data-home-testimonials-track="1"]');
    const slides = Array.from(block.querySelectorAll('[data-home-testimonials-slide]'));
    const dots = Array.from(block.querySelectorAll('[data-home-testimonials-dot]'));
    const prev = block.querySelector('[data-home-testimonials-prev="1"]');
    const next = block.querySelector('[data-home-testimonials-next="1"]');

    if (!track || slides.length < 2) {
      return;
    }

    let activeIndex = 0;

    const goToSlide = (index) => {
      activeIndex = (index + slides.length) % slides.length;
      track.style.transform = `translateX(-${activeIndex * 100}%)`;

      dots.forEach((dot, dotIndex) => {
        dot.classList.toggle('is-active', dotIndex === activeIndex);
      });
    };

    dots.forEach((dot) => {
      dot.addEventListener('click', () => {
        goToSlide(Number(dot.getAttribute('data-home-testimonials-dot')) || 0);
      });
    });

    if (prev) {
      prev.addEventListener('click', () => {
        goToSlide(activeIndex - 1);
      });
    }

    if (next) {
      next.addEventListener('click', () => {
        goToSlide(activeIndex + 1);
      });
    }
  });
})();
