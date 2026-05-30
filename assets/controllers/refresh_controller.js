import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'label', 'spinner'];

    connect() {
        this.element.addEventListener('turbo:submit-start', this.start.bind(this));
        this.element.addEventListener('turbo:submit-end', this.stop.bind(this));
    }

    start() {
        this.buttonTarget.disabled = true;
        this.buttonTarget.classList.add('loading');
        this.spinnerTarget.style.display = 'inline-block';
        this.labelTarget.textContent = 'Fetching...';
    }

    stop() {
        this.buttonTarget.disabled = false;
        this.buttonTarget.classList.remove('loading');
        this.spinnerTarget.style.display = 'none';
        this.labelTarget.textContent = 'Refresh from GitHub';
    }
}
