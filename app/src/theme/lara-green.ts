// Custom theme configuration for PrimeVue with green primary colors
import { definePreset } from '@primeuix/themes'
import Lara from '@primeuix/themes/lara'

export const LaraGreen = definePreset(Lara, {
  semantic: {
    primary: {
      50: '{green.50}',
      100: '{green.100}',
      200: '{green.200}',
      300: '{green.300}',
      400: '{green.400}',
      500: '{green.500}',
      600: '{green.600}',
      700: '{green.700}',
      800: '{green.800}',
      900: '{green.900}',
      950: '{green.950}'
    },
    colorScheme: {
      light: {
        primary: {
          color: '{green.500}',
          inverseColor: '#ffffff',
          hoverColor: '{green.600}',
          activeColor: '{green.700}'
        },
        highlight: {
          background: '{green.50}',
          focusBackground: '{green.100}',
          color: '{green.700}',
          focusColor: '{green.800}'
        }
      },
      dark: {
        primary: {
          color: '{green.400}',
          inverseColor: '{gray.900}',
          hoverColor: '{green.300}',
          activeColor: '{green.200}'
        },
        highlight: {
          background: 'color-mix(in srgb, {green.400}, transparent 84%)',
          focusBackground: 'color-mix(in srgb, {green.400}, transparent 76%)',
          color: 'rgba(255,255,255,.87)',
          focusColor: 'rgba(255,255,255,.87)'
        }
      }
    }
  }
})

export default LaraGreen