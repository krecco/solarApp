/**
 * Users Store
 * Manages user data and operations
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Ref } from 'vue';

/**
 * User Interface
 */
export interface User {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  phone?: string;
  username: string;
  role: 'admin' | 'manager' | 'user' | 'guest';
  status: 'active' | 'inactive' | 'pending' | 'suspended';
  department?: string;
  avatar?: string;
  bio?: string;
  birthDate?: string | Date;
  gender?: string;
  country?: string;
  city?: string;
  permissions?: string[];
  emailNotifications?: boolean;
  smsNotifications?: boolean;
  pushNotifications?: boolean;
  emailVerified?: boolean;
  twoFactorEnabled?: boolean;
  lastLogin?: string | Date;
  loginCount?: number;
  createdAt: string | Date;
  updatedAt: string | Date;
}

/**
 * Users Store
 */
export const useUsersStore = defineStore('users', () => {
  /**
   * State
   */
  const users: Ref<User[]> = ref([]);
  const currentUser: Ref<User | null> = ref(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const searchQuery = ref('');
  const filters = ref({
    status: '',
    role: '',
    department: ''
  });

  /**
   * Mock Data
   */
  const mockUsers: User[] = [
    {
      id: '1',
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      phone: '+1 (555) 123-4567',
      username: 'johndoe',
      role: 'admin',
      status: 'active',
      department: 'engineering',
      avatar: 'https://i.pravatar.cc/150?img=1',
      bio: 'Senior software engineer with 10+ years of experience in full-stack development.',
      birthDate: '1985-06-15',
      gender: 'male',
      country: 'US',
      city: 'San Francisco',
      permissions: ['users.view', 'users.create', 'users.edit', 'users.delete', 'billing.view', 'billing.manage'],
      emailNotifications: true,
      smsNotifications: false,
      pushNotifications: true,
      emailVerified: true,
      twoFactorEnabled: true,
      lastLogin: new Date().toISOString(),
      loginCount: 245,
      createdAt: '2023-01-15T10:30:00Z',
      updatedAt: '2024-02-20T14:45:00Z'
    },
    {
      id: '2',
      firstName: 'Jane',
      lastName: 'Smith',
      email: 'jane.smith@example.com',
      phone: '+1 (555) 234-5678',
      username: 'janesmith',
      role: 'manager',
      status: 'active',
      department: 'sales',
      avatar: 'https://i.pravatar.cc/150?img=2',
      bio: 'Sales manager focused on B2B enterprise solutions.',
      birthDate: '1990-03-22',
      gender: 'female',
      country: 'US',
      city: 'New York',
      permissions: ['users.view', 'billing.view'],
      emailNotifications: true,
      smsNotifications: true,
      pushNotifications: true,
      emailVerified: true,
      twoFactorEnabled: false,
      lastLogin: new Date(Date.now() - 86400000).toISOString(),
      loginCount: 189,
      createdAt: '2023-03-10T08:15:00Z',
      updatedAt: '2024-01-15T11:30:00Z'
    },
    {
      id: '3',
      firstName: 'Bob',
      lastName: 'Johnson',
      email: 'bob.johnson@example.com',
      username: 'bobjohnson',
      role: 'user',
      status: 'active',
      department: 'marketing',
      avatar: 'https://i.pravatar.cc/150?img=3',
      emailNotifications: true,
      smsNotifications: false,
      pushNotifications: false,
      emailVerified: true,
      twoFactorEnabled: false,
      createdAt: '2023-06-01T12:00:00Z',
      updatedAt: '2023-12-10T09:20:00Z'
    },
    {
      id: '4',
      firstName: 'Alice',
      lastName: 'Williams',
      email: 'alice.williams@example.com',
      username: 'alicew',
      role: 'user',
      status: 'pending',
      department: 'support',
      emailNotifications: true,
      smsNotifications: false,
      pushNotifications: true,
      emailVerified: false,
      twoFactorEnabled: false,
      createdAt: '2024-01-20T14:30:00Z',
      updatedAt: '2024-01-20T14:30:00Z'
    },
    {
      id: '5',
      firstName: 'Charlie',
      lastName: 'Brown',
      email: 'charlie.brown@example.com',
      username: 'charlieb',
      role: 'user',
      status: 'suspended',
      department: 'finance',
      emailNotifications: false,
      smsNotifications: false,
      pushNotifications: false,
      emailVerified: true,
      twoFactorEnabled: false,
      createdAt: '2023-09-05T16:45:00Z',
      updatedAt: '2024-02-01T10:00:00Z'
    }
  ];

  /**
   * Getters
   */
  const filteredUsers = computed(() => {
    let result = [...users.value];

    // Apply search
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      result = result.filter(user => 
        user.firstName.toLowerCase().includes(query) ||
        user.lastName.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.username.toLowerCase().includes(query)
      );
    }

    // Apply filters
    if (filters.value.status) {
      result = result.filter(user => user.status === filters.value.status);
    }
    if (filters.value.role) {
      result = result.filter(user => user.role === filters.value.role);
    }
    if (filters.value.department) {
      result = result.filter(user => user.department === filters.value.department);
    }

    return result;
  });

  const totalUsers = computed(() => users.value.length);
  
  const activeUsers = computed(() => 
    users.value.filter(user => user.status === 'active').length
  );
  
  const pendingUsers = computed(() => 
    users.value.filter(user => user.status === 'pending').length
  );
  
  const suspendedUsers = computed(() => 
    users.value.filter(user => user.status === 'suspended').length
  );

  /**
   * Actions
   */
  const fetchUsers = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 500));
      users.value = [...mockUsers];
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch users';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const fetchUser = async (id: string): Promise<User | null> => {
    loading.value = true;
    error.value = null;
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 300));
      
      // Find user in mock data
      const user = mockUsers.find(u => u.id === id);
      if (!user) {
        throw new Error('User not found');
      }
      
      currentUser.value = user;
      return user;
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch user';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const createUser = async (userData: Partial<User>): Promise<User> => {
    loading.value = true;
    error.value = null;
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Create new user
      const newUser: User = {
        id: String(Date.now()),
        firstName: userData.firstName || '',
        lastName: userData.lastName || '',
        email: userData.email || '',
        phone: userData.phone,
        username: userData.username || '',
        role: userData.role || 'user',
        status: userData.status || 'pending',
        department: userData.department,
        avatar: userData.avatar,
        bio: userData.bio,
        birthDate: userData.birthDate,
        gender: userData.gender,
        country: userData.country,
        city: userData.city,
        permissions: userData.permissions || [],
        emailNotifications: userData.emailNotifications ?? true,
        smsNotifications: userData.smsNotifications ?? false,
        pushNotifications: userData.pushNotifications ?? true,
        emailVerified: false,
        twoFactorEnabled: false,
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString()
      };
      
      users.value.push(newUser);
      mockUsers.push(newUser);
      
      return newUser;
    } catch (err: any) {
      error.value = err.message || 'Failed to create user';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateUser = async (id: string, userData: Partial<User>): Promise<User> => {
    loading.value = true;
    error.value = null;
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Find and update user
      const index = users.value.findIndex(u => u.id === id);
      if (index === -1) {
        throw new Error('User not found');
      }
      
      const updatedUser = {
        ...users.value[index],
        ...userData,
        updatedAt: new Date().toISOString()
      };
      
      users.value[index] = updatedUser;
      
      // Update mock data
      const mockIndex = mockUsers.findIndex(u => u.id === id);
      if (mockIndex !== -1) {
        mockUsers[mockIndex] = updatedUser;
      }
      
      if (currentUser.value?.id === id) {
        currentUser.value = updatedUser;
      }
      
      return updatedUser;
    } catch (err: any) {
      error.value = err.message || 'Failed to update user';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const deleteUser = async (id: string): Promise<void> => {
    loading.value = true;
    error.value = null;
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Remove user
      const index = users.value.findIndex(u => u.id === id);
      if (index === -1) {
        throw new Error('User not found');
      }
      
      users.value.splice(index, 1);
      
      // Remove from mock data
      const mockIndex = mockUsers.findIndex(u => u.id === id);
      if (mockIndex !== -1) {
        mockUsers.splice(mockIndex, 1);
      }
      
      if (currentUser.value?.id === id) {
        currentUser.value = null;
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to delete user';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const deleteUsers = async (ids: string[]): Promise<void> => {
    loading.value = true;
    error.value = null;
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Remove users
      users.value = users.value.filter(u => !ids.includes(u.id));
      
      // Remove from mock data
      for (const id of ids) {
        const index = mockUsers.findIndex(u => u.id === id);
        if (index !== -1) {
          mockUsers.splice(index, 1);
        }
      }
      
      if (currentUser.value && ids.includes(currentUser.value.id)) {
        currentUser.value = null;
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to delete users';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const setSearchQuery = (query: string) => {
    searchQuery.value = query;
  };

  const setFilters = (newFilters: Partial<typeof filters.value>) => {
    filters.value = { ...filters.value, ...newFilters };
  };

  const clearFilters = () => {
    filters.value = {
      status: '',
      role: '',
      department: ''
    };
    searchQuery.value = '';
  };

  /**
   * Initialize store
   */
  const initialize = async () => {
    if (users.value.length === 0) {
      await fetchUsers();
    }
  };

  return {
    // State
    users,
    currentUser,
    loading,
    error,
    searchQuery,
    filters,
    
    // Getters
    filteredUsers,
    totalUsers,
    activeUsers,
    pendingUsers,
    suspendedUsers,
    
    // Actions
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    deleteUsers,
    setSearchQuery,
    setFilters,
    clearFilters,
    initialize
  };
});
