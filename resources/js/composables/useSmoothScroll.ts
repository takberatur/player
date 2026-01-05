
export const useSmoothScroll = () => {
  const scrollToAnchor = (id: string, offset: number = 80) => {
    const element = document.querySelector(id);
    if (element) {
      const elementPosition = element.getBoundingClientRect().top;
      const offsetPosition = elementPosition + window.pageYOffset - offset;

      window.scrollTo({
        top: offsetPosition,
        behavior: 'smooth',
      });
    }
  };

  return {
    scrollToAnchor,
  };
};
