import EmergencyContactsSlide from './EmergencyContactsSlide.vue';
import NoticesSlide from './NoticesSlide.vue';
import SocialLinksSlide from './SocialLinksSlide.vue';

/**
 * Display component for each slide type, keyed by `SlideType::key()` on the
 * server. Slides of a type missing here are ignored by the display.
 */
export const slideComponents = {
    emergency_contacts: EmergencyContactsSlide,
    notices: NoticesSlide,
    social_links: SocialLinksSlide,
};
