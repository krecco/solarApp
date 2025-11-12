// Tour steps configuration for the onboarding tour
export interface TourStep {
  id: string
  title: string
  content: string
  target?: string // CSS selector for the element to highlight
  position?: 'top' | 'bottom' | 'left' | 'right' | 'center'
  actions?: {
    skip?: boolean
    next?: boolean
    prev?: boolean
    finish?: boolean
  }
}

export const tourSteps: TourStep[] = [
  {
    id: 'welcome',
    title: 'Welcome to Your Dashboard! 👋',
    content: 'Let\'s take a quick tour to help you get started with all the features available in your account.',
    position: 'center',
    actions: {
      skip: true,
      next: true
    }
  },
  {
    id: 'dashboard-overview',
    title: 'Dashboard Overview',
    content: 'This is your main dashboard where you can see key metrics, recent activity, and quick actions.',
    target: '.dashboard-content',
    position: 'bottom',
    actions: {
      skip: true,
      prev: true,
      next: true
    }
  },
  {
    id: 'navigation-menu',
    title: 'Navigation Menu',
    content: 'Use the sidebar menu to navigate between different sections of your account.',
    target: '.layout-sidebar',
    position: 'right',
    actions: {
      skip: true,
      prev: true,
      next: true
    }
  },
  {
    id: 'profile-menu',
    title: 'Profile & Settings',
    content: 'Access your profile, account settings, and logout from the profile menu in the top right.',
    target: '.layout-topbar-menu',
    position: 'bottom',
    actions: {
      skip: true,
      prev: true,
      next: true
    }
  },
  {
    id: 'subscription-info',
    title: 'Subscription Management',
    content: 'View and manage your subscription plan, billing, and usage limits from the subscription section.',
    target: '[data-tour="subscription-info"]',
    position: 'bottom',
    actions: {
      skip: true,
      prev: true,
      next: true
    }
  },
  {
    id: 'quick-actions',
    title: 'Quick Actions',
    content: 'Use these quick action buttons to perform common tasks without navigating away from the dashboard.',
    target: '.quick-actions-panel',
    position: 'top',
    actions: {
      skip: true,
      prev: true,
      next: true
    }
  },
  {
    id: 'support-resources',
    title: 'Need Help?',
    content: 'Find helpful resources, documentation, and contact support through the help section.',
    target: '.support-resources-panel',
    position: 'top',
    actions: {
      skip: true,
      prev: true,
      next: true
    }
  },
  {
    id: 'complete',
    title: 'Tour Complete! 🎉',
    content: 'You\'re all set! Feel free to explore your dashboard and don\'t hesitate to reach out if you need any help.',
    position: 'center',
    actions: {
      prev: true,
      finish: true
    }
  }
]

// Helper to get step by ID
export const getStepById = (id: string): TourStep | undefined => {
  return tourSteps.find(step => step.id === id)
}

// Helper to get step index
export const getStepIndex = (id: string): number => {
  return tourSteps.findIndex(step => step.id === id)
}

// Helper to get next step
export const getNextStep = (currentId: string): TourStep | undefined => {
  const currentIndex = getStepIndex(currentId)
  if (currentIndex >= 0 && currentIndex < tourSteps.length - 1) {
    return tourSteps[currentIndex + 1]
  }
  return undefined
}

// Helper to get previous step
export const getPrevStep = (currentId: string): TourStep | undefined => {
  const currentIndex = getStepIndex(currentId)
  if (currentIndex > 0) {
    return tourSteps[currentIndex - 1]
  }
  return undefined
}
