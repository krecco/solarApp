# Admin Panel V2

A modern, feature-rich admin dashboard built with Vue 3, PrimeVue, and Vite. Following the latest 2025 UI/UX trends including glassmorphism, dark mode, AI integration readiness, and mobile-first responsive design.

## 🚀 Features

- **Modern Tech Stack**: Vue 3.4+, Vite 5.1+, TypeScript, Pinia
- **UI Framework**: PrimeVue 3.50+ with custom theming
- **Design Trends**: Glassmorphism, Neumorphism, Dark Mode
- **Charts & Visualizations**: Chart.js, ApexCharts, Vue-ChartJS
- **State Management**: Pinia with persistence
- **Authentication**: JWT-based auth with refresh tokens
- **PWA Ready**: Offline support, installable
- **Performance**: Code splitting, lazy loading, compression
- **Testing**: Vitest for unit tests, Playwright for E2E
- **Accessibility**: WCAG 2.1 AA compliant
- **i18n**: Multi-language support with vue-i18n

## 📦 Installation

### Prerequisites

- Node.js >= 18.0.0
- pnpm >= 8.0.0

### Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd my_admin
```

2. Install dependencies:
```bash
pnpm install
```

3. Copy environment variables:
```bash
cp .env.example .env
```

4. Start development server:
```bash
pnpm dev
```

## 🛠️ Development

### Available Scripts

- `pnpm dev` - Start development server
- `pnpm build` - Build for production
- `pnpm preview` - Preview production build
- `pnpm test` - Run tests
- `pnpm lint` - Lint code
- `pnpm format` - Format code with Prettier
- `pnpm type-check` - Run TypeScript type checking

### Project Structure

```
src/
├── api/           # API service layer
├── assets/        # Static assets (images, fonts)
├── components/    # Reusable Vue components
├── composables/   # Vue composition functions
├── layouts/       # Layout components
├── middleware/    # Route guards & middleware
├── plugins/       # Vue plugins configuration
├── router/        # Vue Router configuration
├── stores/        # Pinia stores
├── styles/        # Global styles (SCSS)
├── types/         # TypeScript type definitions
├── utils/         # Utility functions
├── views/         # Page components
└── main.ts        # Application entry point
```

## 🎨 Design System

### Theme Configuration

The application supports multiple themes and customization options:

- **Dark/Light Mode**: Automatic system detection or manual toggle
- **Color Schemes**: 8+ preset themes with custom color picker
- **Glassmorphism Effects**: Modern frosted glass UI elements
- **Responsive Design**: Mobile-first approach with adaptive layouts

### Component Library

Built on PrimeVue 3.50+ with custom extensions:

- DataTable with advanced filtering
- Form components with validation
- Chart widgets
- Dashboard cards
- Navigation components
- Modal and dialog systems

## 🔐 Security

- JWT-based authentication
- Refresh token rotation
- CSRF protection
- XSS prevention
- Input sanitization
- Rate limiting ready
- Role-based access control (RBAC)

## 📊 Performance

- Lighthouse score target: >90
- First Contentful Paint: <1.5s
- Time to Interactive: <3s
- Bundle size: <500KB initial
- Code splitting by route
- Image lazy loading
- Virtual scrolling for large lists

## 🧪 Testing

### Unit Tests (Vitest)
```bash
pnpm test:unit
```

### E2E Tests (Playwright)
```bash
pnpm test:e2e
```

### Coverage Report
```bash
pnpm test:coverage
```

## 📚 Documentation

Detailed documentation for each module:

- [API Documentation](./docs/api.md)
- [Component Library](./docs/components.md)
- [State Management](./docs/state.md)
- [Routing & Guards](./docs/routing.md)
- [Theming Guide](./docs/theming.md)
- [Deployment Guide](./docs/deployment.md)

## 🚀 Deployment

### Build for Production
```bash
pnpm build
```

### Docker Deployment
```bash
docker build -t admin-panel-v2 .
docker run -p 80:80 admin-panel-v2
```

### Environment Variables

See `.env.example` for all available configuration options.

## 🤝 Contributing

Please read [CONTRIBUTING.md](./CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## 📄 License

This project is proprietary and confidential.

## 🙏 Acknowledgments

- Vue.js team for the amazing framework
- PrimeVue for the comprehensive component library
- All contributors and maintainers

---

**Version**: 0.1.0  
**Last Updated**: August 2025  
**Status**: In Development