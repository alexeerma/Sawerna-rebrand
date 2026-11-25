(() => {
  const variationForm = document.querySelector('.sawerna-single-product__purchase .variations_form');
  const mainGallerySizes = '(max-width: 767px) calc(100vw - 20px), 600px';
  const topPrice = document.querySelector('[data-single-product-price="1"]');
  const topSku = document.querySelector('[data-single-product-sku="1"]');
  const topSkuText = document.querySelector('[data-single-product-sku-text="1"]');
  const topBadges = document.querySelector('[data-single-product-badges="1"]');
  const topLocationBadges = document.querySelector('[data-single-product-location-badges="1"]');
  const topLocationBadgeTemplate = topLocationBadges
    ? topLocationBadges.querySelector('[data-single-product-location-badge-template="1"]')
    : null;
  const topStockBadge = document.querySelector('[data-single-product-stock-badge="1"]');
  const topStockBadgeText = document.querySelector('[data-single-product-stock-badge-text="1"]');
  const pdfWrap = document.querySelector('[data-single-product-pdf="1"]');
  const pdfLink = document.querySelector('[data-single-product-pdf-link="1"]');
  const defaultPdfUrl = pdfLink ? pdfLink.getAttribute('href') || '' : '';
  const paymentSummaryTitle = document.querySelector('[data-payment-summary-title="1"]');
  const paymentSummaryDetail = document.querySelector('[data-payment-summary-detail="1"]');

  const paymentDrawer = document.querySelector('[data-payment-drawer="1"]');
  const paymentDrawerOpenButtons = Array.from(document.querySelectorAll('[data-payment-drawer-open="1"]'));
  const paymentDrawerCloseButtons = paymentDrawer ? Array.from(paymentDrawer.querySelectorAll('[data-payment-drawer-close="1"]')) : [];
  let paymentDrawerCloseTimer = null;
  let paymentDrawerLastTrigger = null;

  const extractCurrentPrice = () => {
    if (!topPrice) {
      return 0;
    }

    const preferredNode = topPrice.querySelector('ins .woocommerce-Price-amount bdi')
      || topPrice.querySelector('.woocommerce-Price-amount bdi');
    const rawText = preferredNode ? preferredNode.textContent || '' : topPrice.textContent || '';
    const normalized = rawText
      .replace(/\s/g, '')
      .replace(/[^\d,.-]/g, '')
      .replace(/\.(?=.*\.)/g, '')
      .replace(',', '.');
    const amount = parseFloat(normalized);

    return Number.isFinite(amount) ? amount : 0;
  };

  const formatEuroAmount = (amount) => new Intl.NumberFormat('et-EE', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);

  const jarelmaksProviders = (() => {
    try {
      const raw = paymentDrawer ? paymentDrawer.getAttribute('data-jarelmaks-providers') : null;
      const parsed = raw ? JSON.parse(raw) : null;
      if (Array.isArray(parsed) && parsed.length) return parsed;
    } catch (e) { /* fall through */ }
    return [{ label: 'Inbank – 0% intress', apr: 0 }, { label: 'ESTO – ~24% aastaintress', apr: 24 }];
  })();

  const providerSelectEl = paymentDrawer ? paymentDrawer.querySelector('[data-payment-calc-provider="1"]') : null;
  if (providerSelectEl && jarelmaksProviders.length > 0) {
    providerSelectEl.innerHTML = '';
    jarelmaksProviders.forEach((p, i) => {
      const opt = document.createElement('option');
      opt.value = String(i);
      opt.textContent = String(p.label || '');
      providerSelectEl.appendChild(opt);
    });
  }

  const calcAnnuity = (principal, monthlyRate, months) => {
    if (monthlyRate === 0 || months === 0) return months > 0 ? principal / months : 0;
    const r = monthlyRate;
    const n = months;
    return principal * r * Math.pow(1 + r, n) / (Math.pow(1 + r, n) - 1);
  };

  const getCalcInputs = () => {
    const providerEl = paymentDrawer ? paymentDrawer.querySelector('[data-payment-calc-provider="1"]') : null;
    const periodEl = paymentDrawer ? paymentDrawer.querySelector('[data-payment-calc-period="1"]') : null;
    const downEl = paymentDrawer ? paymentDrawer.querySelector('[data-payment-calc-downpayment="1"]') : null;
    const providerIndex = providerEl ? parseInt(providerEl.value, 10) : 0;
    const providerConfig = jarelmaksProviders[providerIndex] || jarelmaksProviders[0] || { label: '', apr: '0' };
    return {
      providerConfig,
      months: periodEl ? Math.max(1, parseInt(periodEl.value, 10) || 12) : 12,
      downpayment: downEl ? Math.max(0, parseFloat(downEl.value) || 0) : 0,
    };
  };

  const syncPaymentPlans = () => {
    const price = extractCurrentPrice();
    const { providerConfig, months, downpayment } = getCalcInputs();
    const amountEl = paymentDrawer ? paymentDrawer.querySelector('[data-payment-calc-amount="1"]') : null;

    const principal = Math.max(0, price - downpayment);
    const aprPercent = parseFloat(String(providerConfig.apr)) || 0;
    const monthlyRate = aprPercent / 100 / 12;
    const monthly = principal > 0 && months > 0 ? calcAnnuity(principal, monthlyRate, months) : 0;

    if (amountEl) {
      amountEl.textContent = price > 0 && monthly > 0
        ? `${months} × ${formatEuroAmount(monthly)}`
        : '...';
    }

    if (paymentSummaryTitle && paymentSummaryDetail && price > 0 && monthly > 0) {
      paymentSummaryTitle.textContent = `Või al. ${months} × ${formatEuroAmount(monthly)}`;
      paymentSummaryDetail.textContent = `kasutades ${providerConfig.label}`;
    }
  };

  const openPaymentDrawer = (trigger) => {
    if (!paymentDrawer) {
      return;
    }

    if (paymentDrawerCloseTimer) {
      window.clearTimeout(paymentDrawerCloseTimer);
      paymentDrawerCloseTimer = null;
    }

    paymentDrawerLastTrigger = trigger || null;
    paymentDrawer.hidden = false;
    paymentDrawer.classList.add('is-mounted');
    document.body.classList.add('sawerna-selector-drawer-open');
    paymentDrawerOpenButtons.forEach((button) => button.setAttribute('aria-expanded', 'true'));
    window.requestAnimationFrame(() => {
      paymentDrawer.classList.add('is-open');
    });
  };

  const closePaymentDrawer = () => {
    if (!paymentDrawer) {
      return;
    }

    paymentDrawer.classList.remove('is-open');
    document.body.classList.remove('sawerna-selector-drawer-open');
    paymentDrawerOpenButtons.forEach((button) => button.setAttribute('aria-expanded', 'false'));

    paymentDrawerCloseTimer = window.setTimeout(() => {
      paymentDrawer.hidden = true;
      paymentDrawer.classList.remove('is-mounted');

      if (paymentDrawerLastTrigger) {
        paymentDrawerLastTrigger.focus();
      }

      paymentDrawerLastTrigger = null;
      paymentDrawerCloseTimer = null;
    }, 320);
  };

  if (topPrice) {
    topPrice.dataset.initialHtml = topPrice.innerHTML;
  }

  if (topSkuText) {
    topSkuText.dataset.initialText = (topSkuText.textContent || '').trim();
  }

  const locationBadgeIntro = 'Tule tutvu näidisega ';

  const combineLocationBadgeTexts = (texts) => {
    const normalizedTexts = texts
      .map((item) => String(item || '').trim())
      .filter(Boolean);

    if (normalizedTexts.length <= 1) {
      return normalizedTexts;
    }

    const locationTexts = normalizedTexts
      .filter((text) => text.startsWith(locationBadgeIntro))
      .map((text) => text.slice(locationBadgeIntro.length).trim())
      .filter(Boolean);

    if (locationTexts.length === normalizedTexts.length) {
      return [`${locationBadgeIntro}${locationTexts.join(', ')}`];
    }

    return [normalizedTexts.join(', ')];
  };

  const normalizeLocationBadgeTexts = (value) => {
    if (Array.isArray(value)) {
      return combineLocationBadgeTexts(value);
    }

    if (typeof value !== 'string') {
      return [];
    }

    const text = value.trim();
    if (!text) {
      return [];
    }

    return [text];
  };

  const getCurrentLocationBadgeTexts = () => {
    if (!topLocationBadges) {
      return [];
    }

    return Array.from(topLocationBadges.querySelectorAll('[data-single-product-location-badge-text="1"]'))
      .map((node) => (node.textContent || '').trim())
      .filter(Boolean);
  };

  const initialLocationBadgeTexts = getCurrentLocationBadgeTexts();

  const renderLocationBadges = (texts) => {
    if (!topLocationBadges) {
      return;
    }

    const normalizedTexts = normalizeLocationBadgeTexts(texts);
    const existingBadges = Array.from(topLocationBadges.querySelectorAll('[data-single-product-location-badge="1"]'));

    existingBadges.forEach((badge) => badge.remove());

    normalizedTexts.forEach((text) => {
      if (!topLocationBadgeTemplate || !topLocationBadgeTemplate.content.firstElementChild) {
        return;
      }

      const badge = topLocationBadgeTemplate.content.firstElementChild.cloneNode(true);
      const textNode = badge.querySelector('[data-single-product-location-badge-text="1"]');

      if (textNode) {
        textNode.textContent = text;
      }

      topLocationBadges.insertBefore(badge, topLocationBadgeTemplate);
    });

    topLocationBadges.hidden = !normalizedTexts.length;
  };

  if (topStockBadgeText) {
    topStockBadgeText.dataset.initialText = (topStockBadgeText.textContent || '').trim();
  }

  const syncTopBadgesVisibility = () => {
    if (!topBadges) {
      return;
    }

    const hasLocationText = Boolean(getCurrentLocationBadgeTexts().length);
    const hasStockText = Boolean(topStockBadgeText && (topStockBadgeText.textContent || '').trim());

    if (topLocationBadges) {
      topLocationBadges.hidden = !hasLocationText;
    }

    if (topStockBadge) {
      topStockBadge.hidden = !hasStockText;
    }

    topBadges.hidden = !hasLocationText && !hasStockText;
  };

  const updateTopVariationSummary = (variation) => {
    if (topPrice) {
      if (variation && variation.price_html) {
        topPrice.innerHTML = variation.price_html;
      } else if (topPrice.dataset.initialHtml) {
        topPrice.innerHTML = topPrice.dataset.initialHtml;
      }
    }

    if (topSku && topSkuText) {
      let nextText = (topSkuText.dataset.initialText || '').trim();

      if (variation && typeof variation.sku === 'string' && variation.sku.trim()) {
        nextText = variation.sku.trim();
      }

      topSkuText.textContent = nextText;
      topSku.hidden = !nextText;
    }

    if (topLocationBadges) {
      let nextTexts = initialLocationBadgeTexts.slice();

      if (variation && Array.isArray(variation.sawerna_location_badge_texts)) {
        nextTexts = variation.sawerna_location_badge_texts;
      } else if (variation && typeof variation.sawerna_location_badge_text === 'string') {
        nextTexts = normalizeLocationBadgeTexts(variation.sawerna_location_badge_text);
      }

      renderLocationBadges(nextTexts);
    }

    if (topStockBadge && topStockBadgeText) {
      let nextText = (topStockBadgeText.dataset.initialText || '').trim();

      if (variation) {
        const sawernaStockText = typeof variation.sawerna_stock_badge_text === 'string'
          ? variation.sawerna_stock_badge_text.trim()
          : '';

        if (sawernaStockText) {
          nextText = sawernaStockText;
        } else if (variation.is_in_stock === false) {
          nextText = 'Laost otsas';
        } else {
          nextText = '';
        }
      }

      topStockBadgeText.textContent = nextText;
    }

    syncTopBadgesVisibility();
    syncPaymentPlans();
  };

  const isSafePdfUrl = (value) => typeof value === 'string' && /^(https?:)?\/\//i.test(value);

  const updatePdfButton = (variation) => {
    if (!pdfWrap || !pdfLink) return;

    const variationUrl = variation && isSafePdfUrl(variation.sawerna_pdf_url) ? variation.sawerna_pdf_url : '';
    const url = variationUrl || defaultPdfUrl;

    if (url) {
      pdfLink.setAttribute('href', url);
      pdfWrap.hidden = false;
    } else {
      pdfLink.setAttribute('href', '');
      pdfWrap.hidden = true;
    }
  };

  if (window.jQuery && variationForm) {
    window.jQuery(variationForm).on('found_variation', (event, variation) => {
      updateTopVariationSummary(variation);
      updatePdfButton(variation);
    });

    window.jQuery(variationForm).on('reset_data', () => {
      updateTopVariationSummary(null);
      updatePdfButton(null);
    });
  }

  if (paymentDrawer) {
    paymentDrawerOpenButtons.forEach((button) => {
      button.addEventListener('click', () => {
        syncPaymentPlans();
        openPaymentDrawer(button);
      });
    });

    paymentDrawerCloseButtons.forEach((button) => {
      button.addEventListener('click', closePaymentDrawer);
    });

    const calcForm = paymentDrawer.querySelector('[data-payment-calc="1"]');
    if (calcForm) {
      calcForm.addEventListener('change', syncPaymentPlans);
      calcForm.addEventListener('input', syncPaymentPlans);
    }
  }

  const selectorsRoot = document.querySelector('[data-product-attribute-selectors="1"]');
  if (selectorsRoot) {
    const form = variationForm;
    const mobileSelectorQuery = window.matchMedia('(max-width: 767px)');
    const drawer = selectorsRoot.querySelector('[data-selector-drawer="1"]');
    const drawerTitle = drawer ? drawer.querySelector('[data-selector-drawer-title="1"]') : null;
    // Fabric card rendered by PHP but not yet placed (no native fabric row): goes after the THWEPO "kangas" row.
    const pendingFabricCard = selectorsRoot.querySelector('[data-fabric-card-pending="1"]');

    // ThemeHigh EPO: build selector rows and panels from the plugin's inline table
    const thwepoTable = document.querySelector('.sawerna-single-product__purchase table.extra-options');
    if (thwepoTable) {
      const featuresEl = selectorsRoot.querySelector('.sawerna-single-product__features');
      const panelsEl = selectorsRoot.querySelector('.sawerna-single-product__selector-panels');
      const existingArrowImg = selectorsRoot.querySelector('.sawerna-single-product__feature-arrow img');
      const arrowSrc = existingArrowImg ? (existingArrowImg.getAttribute('src') || '') : '';
      const existingInfoIcon = selectorsRoot.querySelector('.sawerna-single-product__info-icon');
      const infoIconHTML = existingInfoIcon ? existingInfoIcon.outerHTML : '';

      // Rows we successfully convert into swatch selectors; only these get hidden
      // from the original table. Everything else (select, text, multiselect, …)
      // stays visible and renders normally below the variation selectors.
      const convertedRows = [];

      Array.from(thwepoTable.querySelectorAll('tr[data-type]')).forEach((tr) => {
        const firstInput = tr.querySelector('input.thwepo-input-field');
        if (!firstInput || !featuresEl || !panelsEl) return;

        const groupName = firstInput.getAttribute('name') || '';
        if (!groupName) return;

        const labelEl = tr.querySelector('.label-tag');
        const sublabelEl = tr.querySelector('.sublabel-tag');
        const label = labelEl ? labelEl.textContent.trim() : groupName;
        const sublabel = sublabelEl ? sublabelEl.textContent.trim() : '';
        const attributeKey = `thwepo_${groupName}`;
        const type = tr.getAttribute('data-type') || '';
        const isImageGroup = type === 'imagegroup';
        const swatchType = isImageGroup ? 'image' : 'label';

        // Row button — same structure as PHP-rendered variation rows
        const rowBtn = document.createElement('button');
        rowBtn.type = 'button';
        rowBtn.className = 'sawerna-single-product__feature-row sawerna-single-product__feature-row--selector is-empty';
        rowBtn.setAttribute('data-selector-row', '1');
        rowBtn.setAttribute('data-attribute-key', attributeKey);
        rowBtn.setAttribute('data-attribute-name', attributeKey);
        rowBtn.setAttribute('aria-haspopup', 'dialog');
        rowBtn.setAttribute('aria-expanded', 'false');

        const titleSpan = document.createElement('span');
        titleSpan.className = 'sawerna-single-product__feature-header';
        titleSpan.innerHTML = `<span class="sawerna-single-product__feature-title-wrap"><span class="sawerna-single-product__feature-title"></span>${infoIconHTML}</span><span class="sawerna-single-product__feature-arrow" aria-hidden="true">${arrowSrc ? `<img src="${arrowSrc}" alt="">` : ''}</span>`;
        titleSpan.querySelector('.sawerna-single-product__feature-title').textContent = label.endsWith(':') ? label : label + ':';

        const bodySpan = document.createElement('span');
        bodySpan.className = 'sawerna-single-product__feature-body';
        const previewSpan = document.createElement('span');
        previewSpan.className = 'sawerna-single-product__feature-icon sawerna-single-product__selector-preview';
        previewSpan.setAttribute('data-selector-preview', '1');
        previewSpan.setAttribute('aria-hidden', 'true');
        const contentSpan = document.createElement('span');
        contentSpan.className = 'sawerna-single-product__feature-content';
        const valueSpan = document.createElement('span');
        valueSpan.className = 'sawerna-single-product__feature-value';
        valueSpan.setAttribute('data-selector-value', '1');
        valueSpan.textContent = sublabel;
        const detailSpan = document.createElement('span');
        detailSpan.className = 'sawerna-single-product__feature-detail';
        detailSpan.setAttribute('data-selector-detail', '1');
        contentSpan.appendChild(valueSpan);
        contentSpan.appendChild(detailSpan);
        bodySpan.appendChild(previewSpan);
        bodySpan.appendChild(contentSpan);
        rowBtn.appendChild(titleSpan);
        rowBtn.appendChild(bodySpan);
        // rowBtn is appended later, only if this row produces at least one swatch option.

        // Panel
        const panel = document.createElement('section');
        panel.className = 'sawerna-single-product__selector-group sawerna-single-product__selector-group--thwepo';
        panel.setAttribute('data-selector-panel-for', attributeKey);
        panel.setAttribute('data-attribute-name', attributeKey);
        panel.setAttribute('data-attribute-label', label);
        panel.hidden = true;

        const currentDiv = document.createElement('div');
        currentDiv.className = 'sawerna-single-product__selector-current';
        currentDiv.hidden = true;
        currentDiv.innerHTML = '<div class="sawerna-single-product__selector-current-preview" data-selector-current-preview="1"></div><div class="sawerna-single-product__selector-current-copy"><div class="sawerna-single-product__selector-current-label">Valitud: <strong data-selector-current-value="1"></strong></div><div class="sawerna-single-product__selector-current-detail" data-selector-current-detail="1"></div></div>';
        panel.appendChild(currentDiv);

        const optionsDiv = document.createElement('div');
        optionsDiv.className = `sawerna-single-product__selector-options sawerna-single-product__selector-options--${swatchType}`;

        Array.from(tr.querySelectorAll('.thwepo-image-group-item, .thwepo-color-palette-item, .thwepo-label-swatch-item, label.thwepo-radio-input-field, .thwepof-option-item, .thwepo-option-wrap')).forEach((item) => {
          const input = item.querySelector('input.thwepo-input-field');
          if (!input) return;

          const value = input.value || '';
          const optLabelEl = item.querySelector('.thwepo-image-label, .thwepo-color-label, .thwepo-radio-label-swatch, .thwepo-label, .thwepof-label');
          const optLabel = optLabelEl ? optLabelEl.textContent.trim() : (item.querySelector('span') ? item.querySelector('span').textContent.trim() : value);
          const imgEl = item.querySelector('img.thwepo-image-box');
          const imgSrc = imgEl ? (imgEl.getAttribute('src') || '') : '';
          const colorBoxEl = item.querySelector('.thwepo-color-box');
          const optColor = colorBoxEl ? (colorBoxEl.style.background || colorBoxEl.style.backgroundColor || '') : '';

          const optPrice = input.getAttribute('data-price') || '';
          const optPriceType = input.getAttribute('data-price-type') || '';
          const optPriceSymbol = (typeof thwepo_public_var !== 'undefined' && thwepo_public_var.price_symbol) ? thwepo_public_var.price_symbol : '';
          let optPriceLabel = '';
          if (optPrice && optPriceType && optPriceType !== 'none') {
            const priceNum = parseFloat(optPrice);
            if (!isNaN(priceNum) && priceNum !== 0) {
              const sign = priceNum > 0 ? '+' : '';
              optPriceLabel = sign + optPriceSymbol + Math.abs(priceNum).toFixed(2);
            }
          }

          const optBtn = document.createElement('button');
          optBtn.type = 'button';
          optBtn.className = 'sawerna-single-product__selector-option';
          optBtn.setAttribute('data-selector-option', '1');
          optBtn.setAttribute('data-thwepo-option', '1');
          optBtn.setAttribute('data-thwepo-group', groupName);
          optBtn.setAttribute('data-attribute-name', attributeKey);
          optBtn.setAttribute('data-attribute-key', attributeKey);
          optBtn.setAttribute('data-option-value', value);
          optBtn.setAttribute('data-option-label', optLabel);
          optBtn.setAttribute('data-option-detail', optPriceLabel);
          optBtn.setAttribute('data-option-price', optPrice);
          optBtn.setAttribute('data-option-color', optColor);
          optBtn.setAttribute('data-option-image', imgSrc);
          optBtn.setAttribute('data-swatch-type', swatchType);
          optBtn.setAttribute('aria-pressed', 'false');

          if (imgSrc) {
            const mediaSpan = document.createElement('span');
            mediaSpan.className = 'sawerna-single-product__selector-option-media';
            const img = document.createElement('img');
            img.className = 'sawerna-single-product__selector-option-img';
            img.src = imgSrc;
            img.alt = optLabel;
            mediaSpan.appendChild(img);
            optBtn.appendChild(mediaSpan);
          }

          const copySpan = document.createElement('span');
          copySpan.className = 'sawerna-single-product__selector-option-copy';
          const labelNode = document.createElement('span');
          labelNode.className = 'sawerna-single-product__selector-option-label';
          labelNode.textContent = optLabel;
          copySpan.appendChild(labelNode);
          if (optPriceLabel) {
            const priceNode = document.createElement('span');
            priceNode.className = 'sawerna-single-product__selector-option-detail';
            priceNode.textContent = optPriceLabel;
            copySpan.appendChild(priceNode);
          }
          optBtn.appendChild(copySpan);

          optionsDiv.appendChild(optBtn);
        });

        // Only convert rows that actually produced swatch options. Rows with no
        // matching option items (select, text, textarea, multiselect, …) are left
        // untouched so they render normally in the table below the selectors.
        if (optionsDiv.children.length === 0) return;

        panel.appendChild(optionsDiv);
        featuresEl.appendChild(rowBtn);
        if (pendingFabricCard && pendingFabricCard.hidden && (label.toLowerCase().includes('kang') || groupName.toLowerCase().includes('kang'))) {
          rowBtn.classList.add('has-fabric-card');
          rowBtn.after(pendingFabricCard);
          pendingFabricCard.hidden = false;
          pendingFabricCard.removeAttribute('data-fabric-card-pending');
        }
        panelsEl.appendChild(panel);
        convertedRows.push(tr);
      });

      // Hide the THWEPO price table rendered as a sibling (visible once price_field is active).
      const thwepoPriceTable = thwepoTable.parentElement
        ? thwepoTable.parentElement.querySelector('.thwepo-price-table')
        : null;
      if (thwepoPriceTable) {
        thwepoPriceTable.style.setProperty('display', 'none', 'important');
        thwepoPriceTable.setAttribute('aria-hidden', 'true');
      }

      // Hide only the rows we converted into swatch selectors.
      convertedRows.forEach((tr) => {
        tr.style.setProperty('display', 'none', 'important');
        tr.setAttribute('aria-hidden', 'true');
      });

      // If every row was converted, hide the now-empty table; otherwise keep it
      // visible so the remaining (non-swatch) fields render regularly.
      const remainingRows = thwepoTable.querySelectorAll('tr[data-type]:not([aria-hidden="true"])');
      if (remainingRows.length === 0) {
        thwepoTable.style.setProperty('display', 'none', 'important');
        thwepoTable.setAttribute('aria-hidden', 'true');
      } else {
        thwepoTable.classList.add('sawerna-single-product__thwepo-extra');
      }
    }

    // --- THWEPO live price update ---
    // Track selected extra prices per group so we can update the displayed product price.
    const thwepoExtraPrices = new Map(); // attributeKey -> numeric extra price
    let thwepoVariationDisplayPrice = 0; // set from WC found_variation; this is the inclusive display price

    const getThwepoBasePrice = () => {
      // Best source: stored from WC found_variation (variation.display_price is the customer-facing price).
      if (thwepoVariationDisplayPrice > 0) return thwepoVariationDisplayPrice;
      // Fallback: read from the visible price element, prefer <ins> for sale prices.
      const containers = [
        document.querySelector('[data-single-product-price="1"]'),
        document.querySelector('.woocommerce-variation-price .price'),
        document.querySelector('.summary > .price'),
      ];
      for (const container of containers) {
        if (!container) continue;
        const insEl = container.querySelector('ins .woocommerce-Price-amount');
        const anyEl = insEl || container.querySelector('.woocommerce-Price-amount');
        if (!anyEl) continue;
        const val = parseFloat(anyEl.textContent.replace(/[^\d,]/g, '').replace(',', '.'));
        if (val > 0) return val;
      }
      return 0;
    };

    const formatThwepoPrice = (amount) => {
      const pd = typeof thwepo_public_var !== 'undefined' ? thwepo_public_var.price_data : null;
      const decimals = pd ? (parseInt(pd.decimals, 10) || 2) : 2;
      const decSep = pd ? (pd.decimal_separator || ',') : ',';
      const thouSep = pd ? (pd.thousand_separator !== undefined ? pd.thousand_separator : ' ') : ' ';
      const symbol = pd ? (pd.currency_symbol || '€') : '€';
      const fmt = pd ? (pd.price_format || '%2$s %1$s') : '%2$s %1$s';
      const fixed = amount.toFixed(decimals);
      const parts = fixed.split('.');
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thouSep);
      const numStr = parts.join(decSep);
      return fmt.replace('%1$s', `<span class="woocommerce-Price-currencySymbol">${symbol}</span>`).replace('%2$s', numStr);
    };

    const updateThwepoDisplayPrice = () => {
      const extra = Array.from(thwepoExtraPrices.values()).reduce((s, v) => s + v, 0);
      const base = getThwepoBasePrice();

      // This theme uses [data-single-product-price="1"] for the custom price display.
      const priceContainer = document.querySelector('[data-single-product-price="1"]')
        || document.querySelector('.woocommerce-variation-price .price')
        || document.querySelector('.summary > .price');
      if (!priceContainer) return;

      if (extra === 0 || !base) {
        if (priceContainer.dataset.thwepoBaseHtml) {
          priceContainer.innerHTML = priceContainer.dataset.thwepoBaseHtml;
          delete priceContainer.dataset.thwepoBaseHtml;
        }
        return;
      }

      if (!priceContainer.dataset.thwepoBaseHtml) {
        priceContainer.dataset.thwepoBaseHtml = priceContainer.innerHTML;
      }

      priceContainer.innerHTML = `<span class="woocommerce-Price-amount amount"><bdi>${formatThwepoPrice(base + extra)}</bdi></span>`;
    };

    // When WC updates the variation price, capture display_price and reset our stored baseline.
    if (window.jQuery && variationForm) {
      window.jQuery(variationForm).on('found_variation', (e, variation) => {
        thwepoVariationDisplayPrice = variation ? (parseFloat(variation.display_price) || 0) : 0;
        const pc = document.querySelector('[data-single-product-price="1"]');
        if (pc) delete pc.dataset.thwepoBaseHtml;
        window.setTimeout(updateThwepoDisplayPrice, 50);
      }).on('reset_data', () => {
        thwepoVariationDisplayPrice = 0;
        const pc = document.querySelector('[data-single-product-price="1"]');
        if (pc) delete pc.dataset.thwepoBaseHtml;
      });
    }

    // In case found_variation already fired before our handler was registered, read from the DOM now.
    if (!thwepoVariationDisplayPrice) {
      const pc = document.querySelector('[data-single-product-price="1"]');
      if (pc) {
        const insEl = pc.querySelector('ins .woocommerce-Price-amount') || pc.querySelector('.woocommerce-Price-amount');
        if (insEl) {
          const parsed = parseFloat(insEl.textContent.replace(/[^\d,]/g, '').replace(',', '.'));
          if (parsed > 0) thwepoVariationDisplayPrice = parsed;
        }
      }
    }

    // --- THWEPO leftover (non-swatch) fields: reflect their extra price in the display ---
    // Swatch options feed thwepoExtraPrices via their click handler; the visible
    // native fields (select, multiselect, text, number, textarea, remaining
    // radios/checkboxes) hook into the same Map so the displayed price updates
    // exactly like the variation-tab options do.
    const thwepoExtraTable = document.querySelector('.sawerna-single-product__purchase table.extra-options');
    if (thwepoExtraTable) {
      const readPrice = (el) => {
        const type = el.getAttribute('data-price-type') || '';
        if (!type || type === 'none') return 0;
        const price = parseFloat(el.getAttribute('data-price') || '0');
        if (isNaN(price)) return 0;
        if (type === 'percentage' || type === 'percentage_inc') {
          return getThwepoBasePrice() * (price / 100);
        }
        return price;
      };

      const computeRowExtra = (row) => {
        let extra = 0;

        // Select / multiselect — sum every selected option's price.
        row.querySelectorAll('select').forEach((select) => {
          Array.from(select.selectedOptions).forEach((opt) => { extra += readPrice(opt); });
        });

        // Any remaining radios / checkboxes.
        row.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked').forEach((input) => {
          extra += readPrice(input);
        });

        // Free-form inputs — charged only when the user entered a value.
        row.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach((input) => {
          const type = input.getAttribute('data-price-type') || '';
          if (!type || type === 'none') return;
          const val = (input.value || '').trim();
          if (!val) return;
          const price = parseFloat(input.getAttribute('data-price') || '0');
          if (isNaN(price)) return;
          const unit = parseFloat(input.getAttribute('data-price-unit') || '0');
          if (unit > 0) {
            const num = parseFloat(val.replace(',', '.'));
            extra += isNaN(num) ? 0 : (num / unit) * price; // per-unit pricing
          } else {
            extra += price; // flat charge for filling the field
          }
        });

        return extra;
      };

      Array.from(thwepoExtraTable.querySelectorAll('tr[data-type]:not([aria-hidden="true"])')).forEach((row, index) => {
        const key = `thwepo_extra_${index}`;
        const sync = () => {
          thwepoExtraPrices.set(key, computeRowExtra(row));
          updateThwepoDisplayPrice();
        };
        row.addEventListener('change', sync);
        row.addEventListener('input', sync);
        sync(); // initialise from any pre-selected defaults
      });
    }

    const rowButtons = Array.from(selectorsRoot.querySelectorAll('[data-selector-row="1"]'));
    const panelGroups = Array.from(selectorsRoot.querySelectorAll('[data-selector-panel-for]'));
    const optionButtons = Array.from(selectorsRoot.querySelectorAll('[data-selector-option="1"]'));
    const closeButtons = drawer ? Array.from(drawer.querySelectorAll('[data-selector-close="1"]')) : [];
    const applyButton = drawer ? drawer.querySelector('[data-selector-apply="1"]') : null;
    let closeTimer = null;
    let activeAttributeKey = '';
    let lastTrigger = null;
    let pendingSelections = new Map();
    let appliedSelections = new Map();

    const getSelect = (attributeName) => {
      if (!form || !attributeName) {
        return null;
      }

      return form.querySelector(`select[name="${attributeName}"]`);
    };

    const getSelectedOptionButton = (attributeName, value) => {
      if (!attributeName) {
        return null;
      }

      return selectorsRoot.querySelector(
        `[data-selector-option="1"][data-attribute-name="${attributeName}"][data-option-value="${CSS.escape(value || '')}"]`,
      );
    };

    const getPanelGroupByKey = (attributeKey) => selectorsRoot.querySelector(`[data-selector-panel-for="${attributeKey}"]`);

    const getOptionButtonByKeyAndValue = (attributeKey, value) => {
      if (!attributeKey) {
        return null;
      }

      return selectorsRoot.querySelector(
        `[data-selector-panel-for="${attributeKey}"] [data-selector-option="1"][data-option-value="${CSS.escape(value || '')}"]`,
      );
    };

    // Escape values read from data attributes before placing them in HTML attributes.
    const escAttr = (value) => String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    const buildPreviewMarkup = (button, fallbackIcon) => {
      if (!button) {
        return '';
      }

      const type = button.getAttribute('data-swatch-type') || 'label';
      const image = escAttr(button.getAttribute('data-option-image') || '');
      const color = escAttr(button.getAttribute('data-option-color') || '');
      const label = escAttr(button.getAttribute('data-option-label') || '');

      if (type === 'image' && image) {
        return `<img src="${image}" alt="${label}">`;
      }

      if (type === 'color' && color) {
        return `<span class="sawerna-single-product__selector-preview-color" style="background-color:${color};"></span>`;
      }

      return fallbackIcon ? fallbackIcon.innerHTML : '';
    };

    const updateOptionAvailability = () => {
      if (!form) {
        return;
      }

      panelGroups.forEach((group) => {
        const attributeName = group.getAttribute('data-attribute-name') || '';
        const select = getSelect(attributeName);
        if (!select) {
          return;
        }

        const allowedValues = new Map();
        Array.from(select.options).forEach((option) => {
          const value = option.value || '';
          if (!value) {
            return;
          }

          allowedValues.set(value, !option.disabled);
        });

        Array.from(group.querySelectorAll('[data-selector-option="1"]')).forEach((button) => {
          const value = button.getAttribute('data-option-value') || '';
          const isEnabled = allowedValues.has(value) ? Boolean(allowedValues.get(value)) : true;

          button.disabled = !isEnabled;
          button.classList.toggle('is-disabled', !isEnabled);
        });
      });
    };

    const syncCurrentSummary = (group, selectedButton) => {
      if (!group) {
        return;
      }

      const currentWrap = group.querySelector('.sawerna-single-product__selector-current');
      const currentValue = group.querySelector('[data-selector-current-value="1"]');
      const currentDetail = group.querySelector('[data-selector-current-detail="1"]');
      const currentPreview = group.querySelector('[data-selector-current-preview="1"]');
      const sourceButton = selectedButton || null;

      if (currentWrap) {
        currentWrap.hidden = !sourceButton;
      }

      if (currentValue) {
        currentValue.textContent = sourceButton ? sourceButton.getAttribute('data-option-label') || '' : '';
      }

      if (currentDetail) {
        currentDetail.textContent = sourceButton ? sourceButton.getAttribute('data-option-detail') || '' : '';
      }

      if (currentPreview) {
        const rowButton = rowButtons.find(
          (row) => row.getAttribute('data-attribute-name') === group.getAttribute('data-attribute-name'),
        );
        const fallbackIcon = rowButton ? rowButton.querySelector('.sawerna-single-product__feature-icon') : null;
        currentPreview.innerHTML = sourceButton ? buildPreviewMarkup(sourceButton, fallbackIcon) : '';
      }
    };

    const syncApplyButtonState = () => {
      if (!applyButton) {
        return;
      }

      const hasPending = Boolean(activeAttributeKey && pendingSelections.get(activeAttributeKey));
      applyButton.disabled = !hasPending;
    };

    const syncGroupSelectionState = (group, sourceButton) => {
      if (!group) {
        return;
      }

      const attributeName = group.getAttribute('data-attribute-name') || '';
      Array.from(group.querySelectorAll('[data-selector-option="1"]')).forEach((button) => {
        const isActive = button === sourceButton;
        button.classList.toggle('is-selected', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });

      syncCurrentSummary(group, sourceButton);

      const rowButton = rowButtons.find((row) => row.getAttribute('data-attribute-name') === attributeName);
      if (rowButton && mobileSelectorQuery.matches) {
        rowButton.classList.toggle('is-pending', Boolean(sourceButton));
      }
    };

    const syncPendingSelectionUI = (attributeKey) => {
      const group = getPanelGroupByKey(attributeKey);
      if (!group) {
        return;
      }

      const pendingValue = pendingSelections.get(attributeKey) || '';
      const sourceButton = pendingValue ? getOptionButtonByKeyAndValue(attributeKey, pendingValue) : null;
      syncGroupSelectionState(group, sourceButton);
      syncApplyButtonState();
    };

    const syncRowsFromSelects = () => {
      rowButtons.forEach((rowButton) => {
        const attributeName = rowButton.getAttribute('data-attribute-name') || '';
        const attributeKey = rowButton.getAttribute('data-attribute-key') || '';
        const select = getSelect(attributeName);
        const valueNode = rowButton.querySelector('[data-selector-value="1"]');
        const detailNode = rowButton.querySelector('[data-selector-detail="1"]');
        const previewNode = rowButton.querySelector('[data-selector-preview="1"]');
        const fallbackIcon = rowButton.querySelector('.sawerna-single-product__feature-icon');
        const panelGroup = selectorsRoot.querySelector(`[data-selector-panel-for="${attributeKey}"]`);

        if (!select || !valueNode || !previewNode) {
          return;
        }

        const selectedValue = select.value || '';
        appliedSelections.set(attributeKey, selectedValue);
        if (!pendingSelections.has(attributeKey) || !mobileSelectorQuery.matches) {
          pendingSelections.set(attributeKey, selectedValue);
        }
        const selectedButton = getSelectedOptionButton(attributeName, selectedValue);
        const sourceButton = selectedButton || null;

        rowButton.classList.toggle('is-empty', !sourceButton);
        valueNode.textContent = sourceButton ? sourceButton.getAttribute('data-option-label') || '' : '';
        if (detailNode) {
          detailNode.textContent = sourceButton ? sourceButton.getAttribute('data-option-detail') || '' : '';
        }
        previewNode.innerHTML = sourceButton ? buildPreviewMarkup(sourceButton, fallbackIcon) : '';

        optionButtons.forEach((button) => {
          if (button.getAttribute('data-attribute-name') !== attributeName) {
            return;
          }

          const isActive = sourceButton === button;
          button.classList.toggle('is-selected', isActive);
          button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        syncCurrentSummary(panelGroup, selectedButton);
      });

      if (activeAttributeKey) {
        syncPendingSelectionUI(activeAttributeKey);
      }
    };

    const openDrawer = (attributeKey, trigger) => {
      if (!drawer) {
        return;
      }

      if (closeTimer) {
        window.clearTimeout(closeTimer);
        closeTimer = null;
      }

      activeAttributeKey = attributeKey;
      lastTrigger = trigger || null;

      const activeGroup = getPanelGroupByKey(attributeKey);
      if (activeGroup) {
        const attributeName = activeGroup.getAttribute('data-attribute-name') || '';
        const select = getSelect(attributeName);
        pendingSelections.set(attributeKey, select ? (select.value || '') : '');
      }

      panelGroups.forEach((group) => {
        const isActive = group.getAttribute('data-selector-panel-for') === attributeKey;
        group.hidden = !isActive;
        group.classList.toggle('is-active', isActive);

        if (isActive && drawerTitle) {
          drawerTitle.textContent = group.getAttribute('data-attribute-label') || '';
        }
      });

      rowButtons.forEach((button) => {
        const isActive = button === trigger;
        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-expanded', isActive ? 'true' : 'false');
      });

      drawer.hidden = false;
      drawer.classList.add('is-mounted');
      document.body.classList.add('sawerna-selector-drawer-open');
      window.requestAnimationFrame(() => {
        drawer.classList.add('is-open');
      });
      syncPendingSelectionUI(attributeKey);
    };

    const closeDrawer = () => {
      if (!drawer) {
        return;
      }

      drawer.classList.remove('is-open');
      document.body.classList.remove('sawerna-selector-drawer-open');
      rowButtons.forEach((button) => {
        button.classList.remove('is-active');
        button.setAttribute('aria-expanded', 'false');
      });
      panelGroups.forEach((group) => {
        group.hidden = true;
        group.classList.remove('is-active');
      });

      closeTimer = window.setTimeout(() => {
        drawer.hidden = true;
        drawer.classList.remove('is-mounted');

        if (lastTrigger) {
          lastTrigger.focus();
        }

        activeAttributeKey = '';
        lastTrigger = null;
        closeTimer = null;
      }, 320);
    };

    rowButtons.forEach((button) => {
      button.addEventListener('click', () => {
        openDrawer(button.getAttribute('data-attribute-key') || '', button);
      });
    });

    const syncThwepoRow = (attributeKey, selectedValue) => {
      const rowBtn = rowButtons.find((r) => r.getAttribute('data-attribute-key') === attributeKey);
      const optBtn = getOptionButtonByKeyAndValue(attributeKey, selectedValue);
      const group = getPanelGroupByKey(attributeKey);

      if (rowBtn) {
        rowBtn.classList.remove('is-empty');
        const valueNode = rowBtn.querySelector('[data-selector-value="1"]');
        const previewNode = rowBtn.querySelector('[data-selector-preview="1"]');
        const optLabel = optBtn ? (optBtn.getAttribute('data-option-label') || '') : '';
        const imgSrc = optBtn ? (optBtn.getAttribute('data-option-image') || '') : '';
        if (valueNode) valueNode.textContent = optLabel;
        if (previewNode) {
          previewNode.textContent = '';
          if (imgSrc) {
            const previewImg = document.createElement('img');
            previewImg.src = imgSrc;
            previewImg.alt = optLabel;
            previewNode.appendChild(previewImg);
          }
        }
      }

      syncGroupSelectionState(group, optBtn || null);
      appliedSelections.set(attributeKey, selectedValue);
      pendingSelections.set(attributeKey, selectedValue);

      // Update displayed product price with selected THWEPO extra
      const rawPrice = optBtn ? parseFloat(optBtn.getAttribute('data-option-price') || '0') : 0;
      thwepoExtraPrices.set(attributeKey, isNaN(rawPrice) ? 0 : rawPrice);
      updateThwepoDisplayPrice();
    };

    // THWEPO rows start unselected (is-empty) — user must pick an option before adding to cart.

    optionButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const attributeName = button.getAttribute('data-attribute-name') || '';
        const attributeKey = button.getAttribute('data-attribute-key') || '';
        const optionValue = button.getAttribute('data-option-value') || '';
        const select = getSelect(attributeName);

        if (button.disabled) {
          return;
        }

        // ThemeHigh option — sync to hidden radio input instead of a WC select
        if (button.getAttribute('data-thwepo-option') === '1') {
          const groupName = button.getAttribute('data-thwepo-group') || '';
          if (thwepoTable && groupName) {
            const hiddenInput = thwepoTable.querySelector(`input[name="${groupName}"][value="${optionValue}"]`);
            if (hiddenInput) {
              hiddenInput.checked = true;
              // Use jQuery trigger so THWEPO's price calculation handlers fire reliably
              if (window.jQuery) {
                window.jQuery(hiddenInput).trigger('change');
              } else {
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
              }
            }
          }

          if (mobileSelectorQuery.matches) {
            pendingSelections.set(attributeKey, optionValue);
            syncPendingSelectionUI(attributeKey);
            return;
          }

          syncThwepoRow(attributeKey, optionValue);
          window.setTimeout(closeDrawer, 30);
          return;
        }

        if (!select) {
          return;
        }

        if (mobileSelectorQuery.matches) {
          pendingSelections.set(attributeKey, optionValue);
          syncPendingSelectionUI(attributeKey);
          return;
        }

        select.value = optionValue;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        window.setTimeout(() => {
          updateOptionAvailability();
          syncRowsFromSelects();
          closeDrawer();
        }, 30);
      });
    });

    if (applyButton) {
      applyButton.addEventListener('click', () => {
        if (!activeAttributeKey) {
          return;
        }

        const group = getPanelGroupByKey(activeAttributeKey);
        if (!group) {
          return;
        }

        const attributeName = group.getAttribute('data-attribute-name') || '';
        const select = getSelect(attributeName);
        const pendingValue = pendingSelections.get(activeAttributeKey) || '';

        if (!pendingValue) {
          return;
        }

        // ThemeHigh group — sync to hidden radio
        if (!select && activeAttributeKey.startsWith('thwepo_')) {
          const groupName = activeAttributeKey.replace('thwepo_', '');
          if (thwepoTable && groupName) {
            const hiddenInput = thwepoTable.querySelector(`input[name="${groupName}"][value="${pendingValue}"]`);
            if (hiddenInput) {
              hiddenInput.checked = true;
              if (window.jQuery) {
                window.jQuery(hiddenInput).trigger('change');
              } else {
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
              }
            }
          }

          syncThwepoRow(activeAttributeKey, pendingValue);
          window.setTimeout(closeDrawer, 30);
          return;
        }

        if (!select) {
          return;
        }

        select.value = pendingValue;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        window.setTimeout(() => {
          updateOptionAvailability();
          syncRowsFromSelects();
          closeDrawer();
        }, 30);
      });
    }

    closeButtons.forEach((button) => {
      button.addEventListener('click', closeDrawer);
    });

    document.addEventListener('keydown', (event) => {
      if (!drawer || drawer.hidden) {
        return;
      }

      if (event.key === 'Escape') {
        closeDrawer();
      }
    });

    if (form) {
      form.addEventListener('change', () => {
        window.setTimeout(() => {
          updateOptionAvailability();
          syncRowsFromSelects();
        }, 20);
      });

      const resetLink = form.querySelector('.reset_variations');
      if (resetLink) {
        resetLink.addEventListener('click', () => {
          window.setTimeout(() => {
            updateOptionAvailability();
            syncRowsFromSelects();
          }, 20);
        });
      }
    }

    if (window.jQuery && form) {
      window.jQuery(form).on('woocommerce_update_variation_values found_variation reset_data', () => {
        updateOptionAvailability();
        syncRowsFromSelects();
      });
    }

    if (typeof mobileSelectorQuery.addEventListener === 'function') {
      mobileSelectorQuery.addEventListener('change', () => {
        pendingSelections = new Map(appliedSelections);
        syncRowsFromSelects();
      });
    } else if (typeof mobileSelectorQuery.addListener === 'function') {
      mobileSelectorQuery.addListener(() => {
        pendingSelections = new Map(appliedSelections);
        syncRowsFromSelects();
      });
    }

    updateOptionAvailability();
    syncRowsFromSelects();
  }

  const gallery = document.querySelector('[data-single-product-gallery="1"]');
  if (gallery) {
    const mobileLightboxQuery = window.matchMedia('(max-width: 767px)');
    const main = gallery.querySelector('[data-gallery-main="1"]');
    const thumbsContainer = gallery.querySelector('[data-gallery-thumbs="1"]');
    const mainPrevButton = gallery.querySelector('[data-gallery-main-nav="prev"]');
    const mainNextButton = gallery.querySelector('[data-gallery-main-nav="next"]');
    const galleryCount = gallery.querySelector('[data-gallery-count="1"]');
    const galleryCountText = gallery.querySelector('[data-gallery-count-text="1"]');
    const galleryProgress = gallery.querySelector('[data-gallery-progress="1"]');
    const thumbs = Array.from(gallery.querySelectorAll('[data-gallery-thumb="1"]'));
    const lightbox = gallery.querySelector('[data-gallery-lightbox="1"]');
    const lightboxImage = lightbox ? lightbox.querySelector('[data-gallery-lightbox-image]') : null;
    const lightboxThumbTrack = lightbox ? lightbox.querySelector('.sawerna-single-product__lightbox-thumbs') : null;
    const lightboxThumbs = lightbox ? Array.from(lightbox.querySelectorAll('[data-gallery-lightbox-thumb="1"]')) : [];
    const closeButton = lightbox ? lightbox.querySelector('[data-gallery-close="1"]') : null;
    const prevButton = lightbox ? lightbox.querySelector('[data-gallery-nav="prev"]') : null;
    const nextButton = lightbox ? lightbox.querySelector('[data-gallery-nav="next"]') : null;
    let transitionTimer = null;
    let pendingImageRequest = 0;
    let activeIndex = thumbs.findIndex((thumb) => thumb.classList.contains('is-active'));
    const defaultIndex = activeIndex >= 0 ? activeIndex : 0;
    let temporaryVariationImage = null;
    let temporaryLightboxThumb = null;
    let lightboxActiveIndex = activeIndex >= 0 ? activeIndex : 0;

    const applyImageSource = (image, fullSrc, fullSrcset, fullSizes, alt) => {
      if (!image || !fullSrc) {
        return;
      }

      image.src = fullSrc;
      image.alt = alt;

      if (fullSrcset) {
        image.srcset = fullSrcset;
      } else {
        image.removeAttribute('srcset');
      }

      if (fullSizes) {
        image.sizes = fullSizes;
      } else {
        image.removeAttribute('sizes');
      }
    };

    const createLightboxItemFromThumb = (thumb) => ({
      fullSrc: thumb.getAttribute('data-full-src') || '',
      fullSrcset: thumb.getAttribute('data-full-srcset') || '',
      fullSizes: thumb.getAttribute('data-full-sizes') || '',
      alt: thumb.getAttribute('data-alt') || '',
      type: 'gallery',
    });

    const getLightboxItems = () => {
      const items = thumbs.map(createLightboxItemFromThumb);

      if (temporaryVariationImage && temporaryVariationImage.fullSrc) {
        return [
          {
            fullSrc: temporaryVariationImage.fullSrc,
            fullSrcset: temporaryVariationImage.fullSrcset || '',
            fullSizes: temporaryVariationImage.fullSizes || mainGallerySizes,
            alt: temporaryVariationImage.alt || '',
            type: 'variation',
          },
          ...items,
        ];
      }

      return items;
    };

    const ensureTemporaryLightboxThumb = () => {
      if (!lightboxThumbTrack || !temporaryVariationImage || !temporaryVariationImage.fullSrc) {
        if (temporaryLightboxThumb) {
          temporaryLightboxThumb.remove();
          temporaryLightboxThumb = null;
        }
        return;
      }

      if (!temporaryLightboxThumb) {
        temporaryLightboxThumb = document.createElement('button');
        temporaryLightboxThumb.type = 'button';
        temporaryLightboxThumb.className = 'sawerna-single-product__lightbox-thumb sawerna-single-product__lightbox-thumb--variation';
        temporaryLightboxThumb.setAttribute('data-gallery-lightbox-temp', '1');
        temporaryLightboxThumb.setAttribute('aria-pressed', 'false');
        lightboxThumbTrack.prepend(temporaryLightboxThumb);
      }

      const temporaryThumbImg = document.createElement('img');
      temporaryThumbImg.src = temporaryVariationImage.fullSrc;
      temporaryThumbImg.alt = temporaryVariationImage.alt || '';
      temporaryThumbImg.loading = 'lazy';
      temporaryThumbImg.decoding = 'async';
      temporaryLightboxThumb.replaceChildren(temporaryThumbImg);
    };

    const syncLightboxImage = (index) => {
      const items = getLightboxItems();
      const item = items[index];

      if (!item || !lightboxImage) {
        return;
      }

      lightboxActiveIndex = index;
      applyImageSource(lightboxImage, item.fullSrc, item.fullSrcset, item.fullSizes, item.alt);

      if (temporaryLightboxThumb) {
        const isActiveTemp = temporaryVariationImage && index === 0;
        temporaryLightboxThumb.classList.toggle('is-active', Boolean(isActiveTemp));
        temporaryLightboxThumb.setAttribute('aria-pressed', isActiveTemp ? 'true' : 'false');
      }

      lightboxThumbs.forEach((thumb, itemIndex) => {
        const offsetIndex = temporaryVariationImage ? itemIndex + 1 : itemIndex;
        const isActive = offsetIndex === index;
        thumb.classList.toggle('is-active', isActive);
        thumb.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    };

    const applyDirectGalleryImage = (imageData = {}) => {
      const mainImage = main ? main.querySelector('img') : null;
      const fullSrc = imageData.full_src || imageData.src || '';
      const fullSrcset = imageData.full_srcset || imageData.srcset || '';
      const fullSizes = mainGallerySizes;
      const alt = imageData.alt || '';

      if (!fullSrc) {
        return;
      }

      temporaryVariationImage = {
        fullSrc,
        fullSrcset,
        fullSizes,
        alt,
      };
      ensureTemporaryLightboxThumb();

      thumbs.forEach((item) => {
        item.classList.remove('is-active');
        item.setAttribute('aria-pressed', 'false');
      });

      lightboxThumbs.forEach((item) => {
        item.classList.remove('is-active');
        item.setAttribute('aria-pressed', 'false');
      });

      if (mainImage) {
        applyImageSource(mainImage, fullSrc, fullSrcset, fullSizes, alt);
        mainImage.classList.remove('is-changing');
      }

      if (lightboxImage) {
        applyImageSource(lightboxImage, fullSrc, fullSrcset, fullSizes, alt);
      }
    };

    const findThumbIndexByVariation = (variation) => {
      if (!variation) {
        return -1;
      }

      const variationImageId = variation.image_id || (variation.image && variation.image.id) || 0;
      const variationFullSrc = variation.image && (variation.image.full_src || variation.image.src) ? (variation.image.full_src || variation.image.src) : '';

      return thumbs.findIndex((thumb) => {
        const thumbImageId = parseInt(thumb.getAttribute('data-image-id') || '0', 10);
        const thumbFullSrc = thumb.getAttribute('data-full-src') || '';

        if (variationImageId && thumbImageId === variationImageId) {
          return true;
        }

        return variationFullSrc && thumbFullSrc === variationFullSrc;
      });
    };

    const syncThumbWindow = (index, immediate = false) => {
      const thumb = thumbs[index];
      if (!thumb || !thumbs.length || !thumbsContainer) {
        return;
      }

      const maxScroll = Math.max(0, thumbsContainer.scrollWidth - thumbsContainer.clientWidth);
      const targetLeft = Math.max(0, thumb.offsetLeft - ((thumbsContainer.clientWidth - thumb.offsetWidth) / 2));
      const left = Math.min(maxScroll, targetLeft);

      if (typeof thumbsContainer.scrollTo === 'function') {
        thumbsContainer.scrollTo({
          left,
          behavior: immediate ? 'auto' : 'smooth',
        });
      } else {
        thumbsContainer.scrollLeft = left;
      }
    };

    const syncImage = (index) => {
      const thumb = thumbs[index];
      if (!thumb) {
        return;
      }

      temporaryVariationImage = null;
      ensureTemporaryLightboxThumb();

      const fullSrc = thumb.getAttribute('data-full-src');
      const fullSrcset = thumb.getAttribute('data-full-srcset') || '';
      const fullSizes = thumb.getAttribute('data-full-sizes') || '';
      const alt = thumb.getAttribute('data-alt') || '';
      const mainImage = main ? main.querySelector('img') : null;

      activeIndex = index;

      thumbs.forEach((item) => {
        item.classList.remove('is-active');
        item.setAttribute('aria-pressed', 'false');
      });

      thumb.classList.add('is-active');
      thumb.setAttribute('aria-pressed', 'true');

      if (mainImage && fullSrc) {
        const preloadImage = new Image();
        const requestId = pendingImageRequest + 1;

        pendingImageRequest = requestId;

        if (fullSrcset) {
          preloadImage.srcset = fullSrcset;
        }

        if (fullSizes) {
          preloadImage.sizes = fullSizes;
        }

        preloadImage.src = fullSrc;

        if (transitionTimer) {
          window.clearTimeout(transitionTimer);
        }

        mainImage.classList.add('is-changing');

        const finishTransition = () => {
          if (requestId !== pendingImageRequest) {
            return;
          }

          applyImageSource(mainImage, fullSrc, fullSrcset, fullSizes, alt);
          transitionTimer = window.setTimeout(() => {
            mainImage.classList.remove('is-changing');
            transitionTimer = null;
          }, 30);
        };

        if (preloadImage.complete) {
          finishTransition();
        } else {
          preloadImage.addEventListener('load', finishTransition, { once: true });
          preloadImage.addEventListener('error', finishTransition, { once: true });
        }
      }

      if (lightboxImage && fullSrc) {
        applyImageSource(lightboxImage, fullSrc, fullSrcset, fullSizes, alt);
      }

      lightboxActiveIndex = index;
      lightboxThumbs.forEach((item, itemIndex) => {
        const isActive = itemIndex === index;
        item.classList.toggle('is-active', isActive);
        item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });

      if (galleryCount) {
        if (galleryCountText) {
          galleryCountText.textContent = `${index + 1}/${thumbs.length}`;
        } else {
          galleryCount.textContent = `${index + 1}/${thumbs.length}`;
        }
      }

      if (galleryProgress && thumbs.length) {
        const progress = ((index + 1) / thumbs.length) * 100;
        galleryProgress.style.width = `${progress}%`;
      }

      syncThumbWindow(index);
    };

    const openLightbox = () => {
      if (!lightbox || mobileLightboxQuery.matches) {
        return;
      }

      lightbox.hidden = false;
      document.body.style.overflow = 'hidden';
      ensureTemporaryLightboxThumb();

      if (temporaryVariationImage && temporaryVariationImage.fullSrc) {
        syncLightboxImage(0);
      } else {
        syncImage(activeIndex >= 0 ? activeIndex : 0);
      }
    };

    const closeLightbox = () => {
      if (!lightbox) {
        return;
      }

      lightbox.hidden = true;
      document.body.style.overflow = '';
    };

    const navigateGallery = (direction) => {
      if (!thumbs.length) {
        return;
      }

      const nextIndex = (activeIndex + direction + thumbs.length) % thumbs.length;
      syncImage(nextIndex);
    };

    const navigateLightbox = (direction) => {
      const items = getLightboxItems();
      if (!items.length) {
        return;
      }

      const nextIndex = (lightboxActiveIndex + direction + items.length) % items.length;
      syncLightboxImage(nextIndex);
    };

    thumbs.forEach((thumb, index) => {
      thumb.addEventListener('click', () => {
        syncImage(index);
      });
    });

    if (main) {
      main.addEventListener('click', openLightbox);
    }

    if (mainPrevButton) {
      mainPrevButton.addEventListener('click', (event) => {
        event.stopPropagation();
        navigateGallery(-1);
      });
    }

    if (mainNextButton) {
      mainNextButton.addEventListener('click', (event) => {
        event.stopPropagation();
        navigateGallery(1);
      });
    }

    if (closeButton) {
      closeButton.addEventListener('click', closeLightbox);
    }

    if (prevButton) {
      prevButton.addEventListener('click', () => navigateLightbox(-1));
    }

    if (nextButton) {
      nextButton.addEventListener('click', () => navigateLightbox(1));
    }

    lightboxThumbs.forEach((thumb) => {
      thumb.addEventListener('click', () => {
        const index = parseInt(thumb.getAttribute('data-lightbox-index') || '0', 10);
        const offsetIndex = temporaryVariationImage ? index + 1 : index;
        syncLightboxImage(offsetIndex);
      });
    });

    if (lightboxThumbTrack) {
      lightboxThumbTrack.addEventListener('click', (event) => {
        const tempThumb = event.target.closest('[data-gallery-lightbox-temp="1"]');
        if (tempThumb) {
          syncLightboxImage(0);
        }
      });
    }

    if (lightbox) {
      lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) {
          closeLightbox();
        }
      });
    }

    if (typeof mobileLightboxQuery.addEventListener === 'function') {
      mobileLightboxQuery.addEventListener('change', (event) => {
        if (event.matches) {
          closeLightbox();
        }
      });
    } else if (typeof mobileLightboxQuery.addListener === 'function') {
      mobileLightboxQuery.addListener((event) => {
        if (event.matches) {
          closeLightbox();
        }
      });
    }

    document.addEventListener('keydown', (event) => {
      if (!lightbox || lightbox.hidden) {
        return;
      }

      if (event.key === 'Escape') {
        closeLightbox();
      }

      if (event.key === 'ArrowLeft') {
        navigateLightbox(-1);
      }

      if (event.key === 'ArrowRight') {
        navigateLightbox(1);
      }
    });

    if (window.jQuery && variationForm) {
      window.jQuery(variationForm).on('found_variation', (event, variation) => {
        const matchedIndex = findThumbIndexByVariation(variation);

        if (matchedIndex >= 0) {
          syncImage(matchedIndex);
          return;
        }

        if (variation && variation.image) {
          applyDirectGalleryImage(variation.image);
        }
      });

      window.jQuery(variationForm).on('reset_data', () => {
        syncImage(defaultIndex);
      });
    }

    syncImage(activeIndex >= 0 ? activeIndex : 0);
    window.requestAnimationFrame(() => {
      syncThumbWindow(activeIndex >= 0 ? activeIndex : 0, true);
    });
  }

  const tabsRoot = document.querySelector('[data-product-tabs="1"]');
  if (tabsRoot) {
    const buttons = Array.from(tabsRoot.querySelectorAll('[data-tab-button]'));
    const panels = Array.from(tabsRoot.querySelectorAll('[data-tab-panel]'));

    const activateTab = (key) => {
      buttons.forEach((button) => {
        const isActive = button.getAttribute('data-tab-button') === key;
        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });

      panels.forEach((panel) => {
        const isActive = panel.getAttribute('data-tab-panel') === key;
        panel.classList.toggle('is-active', isActive);
        panel.hidden = !isActive;
      });
    };

    buttons.forEach((button) => {
      button.addEventListener('click', () => activateTab(button.getAttribute('data-tab-button')));
    });
  }

  const accordionRoot = document.querySelector('[data-product-accordion="1"]');
  if (accordionRoot) {
    const buttons = Array.from(accordionRoot.querySelectorAll('[data-accordion-button]'));
    const panels = Array.from(accordionRoot.querySelectorAll('[data-accordion-panel]'));

    const setAccordionState = (key, isOpen) => {
      const button = accordionRoot.querySelector(`[data-accordion-button="${key}"]`);
      const panel = accordionRoot.querySelector(`[data-accordion-panel="${key}"]`);

      if (!button || !panel) {
        return;
      }

      button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      panel.hidden = !isOpen;
      panel.parentElement?.classList.toggle('is-open', isOpen);
    };

    const collapseAll = () => {
      buttons.forEach((button) => {
        setAccordionState(button.getAttribute('data-accordion-button'), false);
      });
    };

    collapseAll();

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        const key = button.getAttribute('data-accordion-button');
        const isOpen = button.getAttribute('aria-expanded') === 'true';
        setAccordionState(key, !isOpen);
      });
    });

    if (!panels.length) {
      collapseAll();
    }
  }

  document.querySelectorAll('.sawerna-single-product__purchase .quantity').forEach((quantity) => {
    const input = quantity.querySelector('input.qty');
    if (!input) {
      return;
    }

    let minus = quantity.querySelector('.minus');
    let plus = quantity.querySelector('.plus');

    if (!minus) {
      minus = document.createElement('button');
      minus.type = 'button';
      minus.className = 'minus';
      minus.textContent = '−';
      quantity.insertBefore(minus, input);
    }

    if (!plus) {
      plus = document.createElement('button');
      plus.type = 'button';
      plus.className = 'plus';
      plus.textContent = '+';
      quantity.appendChild(plus);
    }

    minus.addEventListener('click', () => {
      const step = parseFloat(input.step || '1') || 1;
      const min = parseFloat(input.min || '1') || 1;
      const current = parseFloat(input.value || String(min)) || min;
      input.value = String(Math.max(min, current - step));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    plus.addEventListener('click', () => {
      const step = parseFloat(input.step || '1') || 1;
      const max = parseFloat(input.max || '0') || 0;
      const min = parseFloat(input.min || '1') || 1;
      const current = parseFloat(input.value || String(min)) || min;
      const next = current + step;
      input.value = String(max > 0 ? Math.min(max, next) : next);
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  document.querySelectorAll('[data-related-products="1"]').forEach((section) => {
    const track = section.querySelector('.sawerna-single-product__related-track');
    const prev = section.querySelector('[data-related-nav="prev"]');
    const next = section.querySelector('[data-related-nav="next"]');
    const mobileQuery = window.matchMedia('(max-width: 767px)');

    if (!track || !prev || !next) {
      return;
    }

    const getStep = () => {
      const card = track.querySelector('.sawerna-single-product__related-item');
      if (!card) {
        return 0;
      }

      const styles = window.getComputedStyle(track);
      const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
      return card.getBoundingClientRect().width + gap;
    };

    const updateButtons = () => {
      const maxScroll = track.scrollWidth - track.clientWidth;
      prev.disabled = track.scrollLeft <= 4;
      next.disabled = track.scrollLeft >= maxScroll - 4;
    };

    prev.addEventListener('click', () => {
      const multiplier = mobileQuery.matches ? 1 : 4;
      track.scrollBy({ left: -(getStep() * multiplier), behavior: 'smooth' });
    });

    next.addEventListener('click', () => {
      const multiplier = mobileQuery.matches ? 1 : 4;
      track.scrollBy({ left: getStep() * multiplier, behavior: 'smooth' });
    });

    track.addEventListener('scroll', updateButtons, { passive: true });
    window.addEventListener('resize', updateButtons);
    if (typeof mobileQuery.addEventListener === 'function') {
      mobileQuery.addEventListener('change', updateButtons);
    } else if (typeof mobileQuery.addListener === 'function') {
      mobileQuery.addListener(updateButtons);
    }
    updateButtons();
  });

  document.addEventListener('keydown', (event) => {
    if (!paymentDrawer || paymentDrawer.hidden) {
      return;
    }

    if (event.key === 'Escape') {
      closePaymentDrawer();
    }
  });

  syncPaymentPlans();
})();
