import DailyScheduleSlide from './DailyScheduleSlide.vue';
import EmergencyContactsSlide from './EmergencyContactsSlide.vue';
import NoticesSlide from './NoticesSlide.vue';
import SocialLinksSlide from './SocialLinksSlide.vue';
import UpcomingProgramsSlide from './UpcomingProgramsSlide.vue';
import WeatherSlide from './WeatherSlide.vue';

/**
 * Display component for each slide type, keyed by `SlideType::key()` on the
 * server. Slides of a type missing here are ignored by the display.
 */
export const slideComponents = {
    daily_schedule: DailyScheduleSlide,
    emergency_contacts: EmergencyContactsSlide,
    notices: NoticesSlide,
    social_links: SocialLinksSlide,
    upcoming_programs: UpcomingProgramsSlide,
    weather: WeatherSlide,
};
