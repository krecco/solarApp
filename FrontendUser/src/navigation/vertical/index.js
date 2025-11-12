export default [
  {
    title: 'Dashboard',
    route: 'home',
    icon: 'HomeIcon',
    resource: 'Dashboard',
    action: 'read',
  },
  {
    title: 'Kunden',
    route: 'users',
    icon: 'UsersIcon',
    resource: 'Users',
    action: 'read',
  },
  {
    title: 'Anlagen',
    route: 'power-plant-list',
    icon: 'ZapIcon',
    resource: 'SolarPlant',
    action: 'read',
  },
  {
    title: 'Unsere Projekte',
    route: 'projects',
    icon: 'ZapIcon',
    resource: 'SolarPlant',
    action: 'read',
  },
  {
    title: 'Rechnungen',
    route: 'invoice-list',
    icon: 'TargetIcon',
  },
  {
    title: 'Mahnungen',
    route: 'reminder-list',
    icon: 'PrinterIcon',
  },
  {
    title: 'WEB Nachrichten',
    route: 'web-info',
    icon: 'FileIcon',
    resource: 'Dashboard',
    action: 'read',
  },
  {
    title: 'Aktivitäten',
    route: 'activity',
    icon: 'ActivityIcon',
    resource: 'Dashboard',
    action: 'read',
  },
  /*
  {
    title: 'GenericView',
    route: 'generic-view',
    icon: 'SettingsIcon',
    resource: 'Dashboard',
    action: 'read',
  },
  */
  {
    title: 'Einstellungen',
    route: 'admin-settings',
    icon: 'SettingsIcon',
    resource: 'Dashboard',
    action: 'read',
  },
]
