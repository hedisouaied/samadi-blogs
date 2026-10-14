import { initTheme } from './theme';
import { initNavigation } from './navigation';
import { initReveal } from './reveal';
import { initToc } from './toc';
import { initReadingProgress } from './reading-progress';
import { initShare } from './share';
import { initForms } from './forms';
import { initUi } from './ui';

function boot() {
    initTheme();
    initNavigation();
    initReveal();
    initToc();
    initReadingProgress();
    initShare();
    initForms();
    initUi();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
