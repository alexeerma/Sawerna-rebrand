(() => {
  const row = document.querySelector('.rm-footer__logoRow');
  if (!row) {
    return;
  }

  const track = row.querySelector('.rm-footer__logoTrack');
  const prevBtns = row.querySelectorAll('.rm-footer__sliderArrow--prev');
  const nextBtns = row.querySelectorAll('.rm-footer__sliderArrow--next');
  if (!track || (!prevBtns.length && !nextBtns.length)) {
    return;
  }

  const group = track.querySelector('.rm-footer__logoGroup');
  if (!group) {
    return;
  }

  const step = 220;

  const getOffset = () => {
    const transform = getComputedStyle(track).transform;
    if (!transform || transform === 'none') {
      return 0;
    }
    const matrix = transform.match(/matrix.*\((.+)\)/);
    if (!matrix) {
      return 0;
    }
    return parseFloat(matrix[1].split(', ')[4]) || 0;
  };

  const shift = (direction) => {
    track.style.animation = 'none';

    const current = getOffset();
    const groupWidth = group.scrollWidth + 50;
    let target = current + (direction * step);

    if (target > 0) {
      target -= groupWidth;
    } else if (target < -groupWidth) {
      target += groupWidth;
    }

    track.style.transition = 'transform 0.4s ease';
    track.style.transform = `translateX(${target}px)`;

    const onEnd = () => {
      track.removeEventListener('transitionend', onEnd);
      track.style.transition = 'none';
      track.style.transform = `translateX(${target}px)`;
    };
    track.addEventListener('transitionend', onEnd);
  };

  prevBtns.forEach((btn) => btn.addEventListener('click', () => shift(1)));
  nextBtns.forEach((btn) => btn.addEventListener('click', () => shift(-1)));
})();
