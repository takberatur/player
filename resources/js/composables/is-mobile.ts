import { useMediaQuery } from "@vueuse/core";

const DEFAULT_MOBILE_BREAKPOINT = 768;

export const isMobile = useMediaQuery(`(max-width: ${DEFAULT_MOBILE_BREAKPOINT}px)`);
