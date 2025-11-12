import { ref, reactive, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

export function useBillingData() {
  const toast = useToast()
  const confirm = useConfirm()
  
  // Current Plan
  const currentPlan = reactive({
    id: 2,
    name: 'PRO',
    displayName: 'Professional Plan',
    description: 'Perfect for growing businesses and teams',
    price: 49,
    billingCycle: 'month',
    trial: false
  })
  
  const nextBillingDate = ref('February 1, 2025')
  
  // Usage Metrics
  const usageMetrics = ref([
    { name: 'API Calls', used: 750, limit: 1000, percentage: 75, info: '250 calls remaining' },
    { name: 'Storage', used: 67, limit: 100, percentage: 67, info: '33 GB available' },
    { name: 'Team Members', used: 18, limit: 25, percentage: 72, info: '7 seats available' },
    { name: 'Projects', used: 8, limit: 10, percentage: 80, info: '2 projects remaining' }
  ])
  
  // Payment Methods
  const paymentMethods = ref([
    {
      id: 1,
      brand: 'Visa',
      last4: '4242',
      name: 'John Doe',
      expiry: '12/25',
      isDefault: true
    },
    {
      id: 2,
      brand: 'Mastercard',
      last4: '5555',
      name: 'John Doe',
      expiry: '06/26',
      isDefault: false
    }
  ])
  
  // Billing History
  const billingHistory = ref([
    {
      id: 1,
      date: new Date('2024-12-01'),
      description: 'Professional Plan',
      period: 'Dec 1 - Dec 31, 2024',
      amount: 49,
      status: 'paid',
      type: 'subscription'
    },
    {
      id: 2,
      date: new Date('2024-11-01'),
      description: 'Professional Plan',
      period: 'Nov 1 - Nov 30, 2024',
      amount: 49,
      status: 'paid',
      type: 'subscription'
    },
    {
      id: 3,
      date: new Date('2024-10-15'),
      description: 'Additional Storage',
      period: 'One-time purchase',
      amount: 9.99,
      status: 'paid',
      type: 'addon'
    }
  ])
  
  // Billing Info
  const billingInfo = reactive({
    companyName: 'Acme Corp',
    taxId: '12-3456789',
    email: 'billing@acme.com',
    address: '123 Main Street',
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94105',
    country: 'US',
    currency: 'USD'
  })
  
  // Preferences
  const preferences = reactive({
    autoRenewal: true,
    usageAlerts: true,
    invoiceReminders: true,
    billingCycle: 'monthly',
    retryAttempts: 3
  })
  
  // Computed
  const totalSpent = computed(() => {
    return billingHistory.value
      .filter(item => item.status === 'paid')
      .reduce((sum, item) => sum + item.amount, 0)
      .toFixed(2)
  })
  
  const averageMonthly = computed(() => {
    return (parseFloat(totalSpent.value) / 12).toFixed(2)
  })
  
  const lastPaymentDate = computed(() => {
    const lastPaid = billingHistory.value.find(item => item.status === 'paid')
    return lastPaid ? formatDate(lastPaid.date) : 'N/A'
  })
  
  // Methods
  const formatDate = (date) => {
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }).format(date)
  }
  
  const setDefaultPayment = (id) => {
    paymentMethods.value.forEach(method => {
      method.isDefault = method.id === id
    })
    
    toast.add({
      severity: 'success',
      summary: 'Default Payment Method',
      detail: 'Default payment method updated successfully',
      life: 3000
    })
  }
  
  const deletePaymentMethod = (id) => {
    confirm.require({
      message: 'Are you sure you want to delete this payment method?',
      header: 'Delete Payment Method',
      icon: 'pi pi-exclamation-triangle',
      acceptClass: 'p-button-danger',
      accept: () => {
        paymentMethods.value = paymentMethods.value.filter(m => m.id !== id)
        
        toast.add({
          severity: 'success',
          summary: 'Payment Method Deleted',
          detail: 'Payment method has been removed',
          life: 3000
        })
      }
    })
  }
  
  const confirmCancelSubscription = () => {
    confirm.require({
      message: 'Are you sure you want to cancel your subscription? You will lose access to premium features at the end of the billing period.',
      header: 'Cancel Subscription',
      icon: 'pi pi-exclamation-triangle',
      acceptClass: 'p-button-danger',
      accept: () => {
        toast.add({
          severity: 'info',
          summary: 'Subscription Cancelled',
          detail: 'Your subscription will remain active until the end of the billing period',
          life: 5000
        })
      }
    })
  }
  
  const saveBillingInfo = async () => {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    toast.add({
      severity: 'success',
      summary: 'Billing Information Updated',
      detail: 'Your billing information has been saved',
      life: 3000
    })
  }
  
  const savePreferences = async () => {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    toast.add({
      severity: 'success',
      summary: 'Preferences Updated',
      detail: 'Your billing preferences have been saved',
      life: 3000
    })
  }
  
  return {
    currentPlan,
    nextBillingDate,
    usageMetrics,
    paymentMethods,
    billingHistory,
    billingInfo,
    preferences,
    totalSpent,
    averageMonthly,
    lastPaymentDate,
    formatDate,
    setDefaultPayment,
    deletePaymentMethod,
    confirmCancelSubscription,
    saveBillingInfo,
    savePreferences
  }
}
