import { onBeforeUnmount, onMounted } from 'vue';

/**
 * Keyboard shortcuts for checking the display by hand. On air nobody has a
 * keyboard, so they never interfere with the rotation.
 *
 * - Right / Down arrow: next slide.
 * - Left / Up arrow: previous slide.
 * - G: show or hide the safe area guides.
 *
 * @param {{ next: () => void, previous: () => void, toggleGuides: () => void }} actions
 */
export function useKeyboardControls({ next, previous, toggleGuides }) {
    const bindings = {
        ArrowRight: next,
        ArrowDown: next,
        ArrowLeft: previous,
        ArrowUp: previous,
        g: toggleGuides,
        G: toggleGuides,
    };

    function onKeydown(event) {
        const action = bindings[event.key];

        if (!action || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }

        event.preventDefault();
        action();
    }

    onMounted(() => window.addEventListener('keydown', onKeydown));
    onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
}
