<template>
  <div class="page-container">
    <!-- Modern Page Header with Back Button -->
    <div class="page-header-modern" v-if="investment">
      <div class="header-content">
        <div class="header-text">
          <div class="header-title-row">
            <Button
              icon="pi pi-arrow-left"
              severity="secondary"
              text
              rounded
              @click="router.push({ name: 'AdminInvestmentList' })"
              v-tooltip.top="'Back to List'"
              class="back-btn"
            />
            <h1 class="header-title">
              <i class="pi pi-wallet"></i>
              Investment #{{ investment.id.substring(0, 8) }}
            </h1>
            <Button
              icon="pi pi-refresh"
              severity="secondary"
              text
              rounded
              @click="fetchInvestment"
              v-tooltip.top="'Refresh'"
              :loading="loading"
              class="refresh-inline-btn"
            />
          </div>
          <p class="header-subtitle">
            {{ investment.user?.name || 'Unknown Investor' }} - {{ formatCurrency(investment.amount) }}
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card-modern">
            <span class="stat-value">
              <i :class="investment.verified ? 'pi pi-check-circle' : 'pi pi-clock'" class="mr-2"></i>
              {{ investment.verified ? 'Verified' : 'Pending' }}
            </span>
            <span class="stat-label">Verification</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">
              <i class="pi pi-tag mr-2"></i>
              {{ capitalizeFirst(investment.status) }}
            </span>
            <span class="stat-label">Status</span>
          </div>
          <div class="stat-card-modern">
            <span class="stat-value">
              {{ completionPercentage.toFixed(0) }}%
            </span>
            <span class="stat-label">Completed</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <Button
          label="Verify"
          icon="pi pi-check-circle"
          severity="success"
          @click="handleVerify"
          v-if="!investment.verified && (isAdmin || isManager)"
        />
        <Button
          label="Edit"
          icon="pi pi-pencil"
          severity="primary"
          @click="editMode = true"
          v-if="isAdmin || isManager"
          class="add-user-btn"
        />
      </div>
    </div>

    <div v-if="loading && !investment" class="flex justify-content-center py-8">
      <ProgressSpinner />
    </div>

    <div v-else-if="investment" class="grid">
      <!-- Main Content -->
      <div class="col-12 lg:col-8">
        <!-- Investment Overview -->
        <Card class="mb-3">
          <template #title>
            <div class="flex justify-content-between align-items-center">
              <span>Investment Overview</span>
              <div class="flex gap-2">
                <Tag :value="investment.status" :severity="getStatusSeverity(investment.status)" />
                <Tag
                  v-if="investment.verified"
                  value="Verified"
                  severity="success"
                  icon="pi pi-check-circle"
                />
                <Tag
                  v-else
                  value="Pending Verification"
                  severity="warning"
                  icon="pi pi-clock"
                />
              </div>
            </div>
          </template>
          <template #content>
            <div class="grid">
              <div class="col-12 md:col-6">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Investment ID</label>
                  <div class="font-mono text-sm">{{ investment.id }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Investment Amount</label>
                  <div class="text-2xl font-bold text-primary">{{ formatCurrency(investment.amount) }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Interest Rate</label>
                  <div class="font-semibold text-lg">{{ investment.interest_rate }}% per year</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Duration</label>
                  <div class="font-semibold">
                    {{ investment.duration_months }} months
                    ({{ Math.floor(investment.duration_months / 12) }} years
                    {{ investment.duration_months % 12 > 0 ? `${investment.duration_months % 12} months` : '' }})
                  </div>
                </div>
              </div>
              <div class="col-12 md:col-6">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Total Interest</label>
                  <div class="text-xl font-bold text-green-600">{{ formatCurrency(investment.total_interest || 0) }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Total Return</label>
                  <div class="text-2xl font-bold text-green-600">{{ formatCurrency(investment.total_repayment || 0) }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Repayment Interval</label>
                  <div class="font-semibold">{{ capitalizeFirst(investment.repayment_interval) }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Contract Status</label>
                  <div class="font-semibold">{{ investment.contract_status || 'Pending' }}</div>
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Investor Information -->
        <Card class="mb-3" v-if="investment.user">
          <template #title>Investor Information</template>
          <template #content>
            <div class="flex align-items-start gap-4">
              <Avatar :label="getInitials(investment.user.name)" size="xlarge" shape="circle" />
              <div class="flex-1">
                <div class="grid">
                  <div class="col-12 md:col-6">
                    <div class="field mb-3">
                      <label class="text-sm text-gray-500">Name</label>
                      <div class="font-semibold text-lg">{{ investment.user.name }}</div>
                    </div>
                    <div class="field mb-3">
                      <label class="text-sm text-gray-500">Email</label>
                      <div>{{ investment.user.email }}</div>
                    </div>
                  </div>
                  <div class="col-12 md:col-6">
                    <div class="field mb-3">
                      <label class="text-sm text-gray-500">Customer Number</label>
                      <div class="font-semibold">{{ investment.user.customer_no || 'N/A' }}</div>
                    </div>
                    <div class="field mb-3">
                      <label class="text-sm text-gray-500">Customer Type</label>
                      <div class="font-semibold">{{ investment.user.customer_type || 'N/A' }}</div>
                    </div>
                  </div>
                </div>
                <div class="flex gap-2 mt-2">
                  <Button
                    label="View Profile"
                    icon="pi pi-user"
                    severity="info"
                    size="small"
                    @click="viewUserProfile(investment.user.id)"
                  />
                  <Button
                    label="Send Email"
                    icon="pi pi-envelope"
                    severity="secondary"
                    size="small"
                    @click="handleSendEmail(investment.user.email)"
                  />
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Solar Plant Information -->
        <Card class="mb-3" v-if="investment.solar_plant">
          <template #title>Solar Plant Information</template>
          <template #content>
            <div class="flex align-items-start gap-4">
              <i class="pi pi-sun text-6xl text-primary-300"></i>
              <div class="flex-1">
                <div class="grid">
                  <div class="col-12 md:col-6">
                    <div class="field mb-3">
                      <label class="text-sm text-gray-500">Plant Name</label>
                      <div class="font-semibold text-lg">{{ investment.solar_plant.title }}</div>
                    </div>
                    <div class="field mb-3">
                      <label class="text-sm text-gray-500">Location</label>
                      <div>{{ investment.solar_plant.location || 'N/A' }}</div>
                    </div>
                  </div>
                  <div class="col-12 md:col-6">
                    <div class="field mb-3">
                      <label class="text-sm text-gray-500">Nominal Power</label>
                      <div class="font-semibold">{{ investment.solar_plant.nominal_power }} kWp</div>
                    </div>
                    <div class="field mb-3">
                      <label class="text-sm text-gray-500">Status</label>
                      <Tag :value="investment.solar_plant.status" :severity="getStatusSeverity(investment.solar_plant.status)" />
                    </div>
                  </div>
                </div>
                <Button
                  label="View Plant Details"
                  icon="pi pi-external-link"
                  severity="info"
                  size="small"
                  @click="router.push({ name: 'AdminSolarPlantDetail', params: { id: investment.solar_plant_id } })"
                />
              </div>
            </div>
          </template>
        </Card>

        <!-- Timeline & Verification -->
        <Card class="mb-3">
          <template #title>Timeline & Verification</template>
          <template #content>
            <div class="grid">
              <div class="col-12 md:col-6">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Created</label>
                  <div class="font-semibold">{{ formatDate(investment.created_at) }}</div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Start Date</label>
                  <div class="font-semibold">
                    {{ investment.start_date ? formatDate(investment.start_date) : 'Not started' }}
                  </div>
                </div>
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">End Date</label>
                  <div class="font-semibold">
                    {{ investment.end_date ? formatDate(investment.end_date) : 'N/A' }}
                  </div>
                </div>
              </div>
              <div class="col-12 md:col-6">
                <div class="field mb-3">
                  <label class="text-sm text-gray-500">Verification Status</label>
                  <div>
                    <Tag
                      v-if="investment.verified"
                      value="Verified"
                      severity="success"
                      icon="pi pi-check-circle"
                    />
                    <Tag
                      v-else
                      value="Pending"
                      severity="warning"
                      icon="pi pi-clock"
                    />
                  </div>
                </div>
                <div class="field mb-3" v-if="investment.verified">
                  <label class="text-sm text-gray-500">Verified At</label>
                  <div class="font-semibold">{{ formatDate(investment.verified_at) }}</div>
                </div>
                <div class="field mb-3" v-if="investment.verified_by_user">
                  <label class="text-sm text-gray-500">Verified By</label>
                  <div class="font-semibold">{{ investment.verified_by_user.name }}</div>
                </div>
              </div>
            </div>

            <Divider />

            <div v-if="!investment.verified" class="bg-orange-50 p-3 border-round">
              <div class="flex align-items-center justify-content-between">
                <div>
                  <div class="font-semibold text-orange-800 mb-1">Action Required</div>
                  <div class="text-sm text-orange-600">This investment requires verification before it can be activated.</div>
                </div>
                <Button
                  label="Verify Now"
                  icon="pi pi-check-circle"
                  severity="success"
                  @click="handleVerify"
                  v-if="isAdmin || isManager"
                />
              </div>
            </div>
          </template>
        </Card>

        <!-- Repayment Progress -->
        <Card class="mb-3">
          <template #title>Repayment Progress</template>
          <template #content>
            <div class="mb-4">
              <div class="flex justify-content-between mb-2">
                <span class="text-sm text-gray-500">Amount Paid</span>
                <span class="font-semibold">
                  {{ formatCurrency(investment.paid_amount) }} / {{ formatCurrency(investment.total_repayment || 0) }}
                </span>
              </div>
              <ProgressBar :value="completionPercentage" :showValue="false" />
              <div class="text-sm text-gray-500 mt-1">{{ completionPercentage.toFixed(1) }}% completed</div>
            </div>

            <div class="grid">
              <div class="col-12 md:col-4">
                <div class="text-center p-3 border-round bg-primary-50">
                  <div class="text-sm text-gray-600 mb-1">Paid</div>
                  <div class="text-xl font-bold text-primary">{{ formatCurrency(investment.paid_amount) }}</div>
                </div>
              </div>
              <div class="col-12 md:col-4">
                <div class="text-center p-3 border-round bg-orange-50">
                  <div class="text-sm text-gray-600 mb-1">Remaining</div>
                  <div class="text-xl font-bold text-orange-600">{{ formatCurrency(remainingBalance) }}</div>
                </div>
              </div>
              <div class="col-12 md:col-4">
                <div class="text-center p-3 border-round bg-green-50">
                  <div class="text-sm text-gray-600 mb-1">Total Return</div>
                  <div class="text-xl font-bold text-green-600">{{ formatCurrency(investment.total_repayment || 0) }}</div>
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Notes -->
        <Card v-if="investment.notes" class="mb-3">
          <template #title>Investment Notes</template>
          <template #content>
            <div class="white-space-pre-wrap">{{ investment.notes }}</div>
          </template>
        </Card>

        <!-- Repayment Schedule -->
        <Card v-if="investment.repayments && investment.repayments.length > 0">
          <template #title>
            <div class="flex justify-content-between align-items-center">
              <span>Repayment Schedule ({{ investment.repayments.length }} payments)</span>
              <Button
                :icon="showRepayments ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
                text
                rounded
                @click="showRepayments = !showRepayments"
                v-tooltip.top="showRepayments ? 'Collapse' : 'Expand'"
              />
            </div>
          </template>
          <template #content>
            <div v-show="showRepayments" class="repayment-schedule-container">
            <DataTable :value="investment.repayments" stripedRows scrollable scrollHeight="400px">
              <Column field="due_date" header="Due Date" sortable>
                <template #body="{ data }">
                  {{ formatDate(data.due_date) }}
                </template>
              </Column>
              <Column field="amount" header="Amount">
                <template #body="{ data }">
                  {{ formatCurrency(data.amount) }}
                </template>
              </Column>
              <Column field="paid_amount" header="Paid">
                <template #body="{ data }">
                  {{ formatCurrency(data.paid_amount || 0) }}
                </template>
              </Column>
              <Column field="status" header="Status">
                <template #body="{ data }">
                  <Tag :value="data.status" :severity="getRepaymentStatusSeverity(data.status)" />
                </template>
              </Column>
              <Column field="paid_at" header="Paid Date">
                <template #body="{ data }">
                  <span v-if="data.paid_at">{{ formatDate(data.paid_at) }}</span>
                  <span v-else class="text-gray-400">-</span>
                </template>
              </Column>
              <Column header="Actions" frozen alignFrozen="right">
                <template #body="{ data }">
                  <Button
                    icon="pi pi-check"
                    severity="success"
                    text
                    rounded
                    v-tooltip.top="'Mark as Paid'"
                    :disabled="data.status === 'paid'"
                    @click="handleMarkRepaymentAsPaid(data)"
                  />
                </template>
              </Column>
            </DataTable>
            </div>
          </template>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="col-12 lg:col-4">
        <!-- Quick Actions -->
        <Card class="mb-3">
          <template #title>Quick Actions</template>
          <template #content>
            <div class="flex flex-column gap-2">
              <Button
                label="Verify Investment"
                icon="pi pi-check-circle"
                severity="success"
                class="w-full"
                @click="handleVerify"
                :disabled="investment.verified"
                v-if="isAdmin || isManager"
              />
              <Button
                label="Generate Contract"
                icon="pi pi-file-pdf"
                severity="info"
                class="w-full"
                :disabled="!investment.verified"
                @click="handleGenerateContract"
                :loading="generatingPdf"
              />
              <Button
                label="Send Notification"
                icon="pi pi-send"
                severity="secondary"
                class="w-full"
                @click="notificationDialog = true"
              />
              <Button
                label="Download Documents"
                icon="pi pi-download"
                severity="secondary"
                class="w-full"
                @click="handleDownloadDocuments"
              />
              <Divider />
              <Button
                label="Cancel Investment"
                icon="pi pi-times-circle"
                severity="danger"
                class="w-full"
                outlined
                @click="cancelDialog = true"
              />
            </div>
          </template>
        </Card>

        <!-- Status Summary -->
        <Card class="mb-3">
          <template #title>Status Summary</template>
          <template #content>
            <div class="flex flex-column gap-3">
              <div class="flex align-items-center justify-content-between pb-3 border-bottom-1 border-gray-200">
                <div>
                  <div class="text-sm text-gray-500">Investment Status</div>
                  <div class="font-semibold">{{ capitalizeFirst(investment.status) }}</div>
                </div>
                <Tag :value="investment.status" :severity="getStatusSeverity(investment.status)" />
              </div>

              <div class="flex align-items-center justify-content-between pb-3 border-bottom-1 border-gray-200">
                <div>
                  <div class="text-sm text-gray-500">Verification</div>
                  <div class="font-semibold">{{ investment.verified ? 'Verified' : 'Pending' }}</div>
                </div>
                <i
                  :class="investment.verified ? 'pi pi-check-circle text-green-500' : 'pi pi-clock text-orange-500'"
                  style="font-size: 2rem"
                ></i>
              </div>

              <div class="flex align-items-center justify-content-between pb-3 border-bottom-1 border-gray-200">
                <div>
                  <div class="text-sm text-gray-500">Contract</div>
                  <div class="font-semibold">{{ investment.contract_status || 'Pending' }}</div>
                </div>
                <i class="pi pi-file text-2xl text-primary-300"></i>
              </div>

              <div class="flex align-items-center justify-content-between">
                <div>
                  <div class="text-sm text-gray-500">Completion</div>
                  <div class="font-semibold">{{ completionPercentage.toFixed(0) }}%</div>
                </div>
                <i class="pi pi-chart-pie text-2xl text-primary-300"></i>
              </div>
            </div>
          </template>
        </Card>

        <!-- Financial Summary -->
        <Card class="mb-3">
          <template #title>Financial Summary</template>
          <template #content>
            <div class="flex flex-column gap-3">
              <div class="flex justify-content-between">
                <span class="text-gray-600">Principal</span>
                <span class="font-semibold">{{ formatCurrency(investment.amount) }}</span>
              </div>
              <div class="flex justify-content-between">
                <span class="text-gray-600">Interest ({{ investment.interest_rate }}%)</span>
                <span class="font-semibold text-green-600">{{ formatCurrency(investment.total_interest || 0) }}</span>
              </div>
              <Divider />
              <div class="flex justify-content-between">
                <span class="text-gray-600 font-semibold">Total Return</span>
                <span class="font-bold text-xl text-primary">{{ formatCurrency(investment.total_repayment || 0) }}</span>
              </div>
              <Divider />
              <div class="flex justify-content-between">
                <span class="text-gray-600">Paid to Date</span>
                <span class="font-semibold text-primary">{{ formatCurrency(investment.paid_amount) }}</span>
              </div>
              <div class="flex justify-content-between">
                <span class="text-gray-600">Remaining</span>
                <span class="font-semibold text-orange-600">{{ formatCurrency(remainingBalance) }}</span>
              </div>
            </div>
          </template>
        </Card>

        <!-- Activity Log -->
        <Card>
          <template #title>Recent Activity</template>
          <template #content>
            <Timeline :value="activityLog">
              <template #content="slotProps">
                <div class="text-sm">
                  <div class="font-semibold">{{ slotProps.item.title }}</div>
                  <div class="text-gray-500">{{ slotProps.item.description }}</div>
                  <div class="text-xs text-gray-400 mt-1">{{ slotProps.item.timestamp }}</div>
                </div>
              </template>
            </Timeline>
          </template>
        </Card>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <i class="pi pi-exclamation-triangle text-5xl text-orange-400 mb-3"></i>
      <div class="text-xl text-gray-500">Investment not found</div>
    </div>

    <!-- Verify Confirmation Dialog -->
    <Dialog
      v-model:visible="verifyDialog"
      :style="{ width: '450px' }"
      header="Verify Investment"
      :modal="true"
    >
      <div class="confirmation-content">
        <i class="pi pi-check-circle mr-3" style="font-size: 2rem; color: var(--green-500)" />
        <span v-if="investment">
          Are you sure you want to verify this investment of
          <b>{{ formatCurrency(investment.amount) }}</b>?
          This action will activate the investment and start the repayment schedule.
        </span>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" text @click="verifyDialog = false" />
        <Button
          label="Verify"
          icon="pi pi-check"
          severity="success"
          @click="verifyInvestment"
          :loading="verifying"
        />
      </template>
    </Dialog>

    <!-- Edit Investment Dialog -->
    <Dialog
      v-model:visible="editMode"
      :style="{ width: '700px' }"
      header="Edit Investment"
      :modal="true"
    >
      <div v-if="investment" class="grid">
        <div class="col-12 md:col-6">
          <label for="edit_amount" class="block mb-2">Investment Amount (€)</label>
          <InputNumber
            id="edit_amount"
            v-model="editForm.amount"
            class="w-full"
            mode="currency"
            currency="EUR"
            locale="de-DE"
          />
        </div>
        <div class="col-12 md:col-6">
          <label for="edit_duration" class="block mb-2">Duration (months)</label>
          <InputNumber
            id="edit_duration"
            v-model="editForm.duration_months"
            class="w-full"
            :min="1"
          />
        </div>
        <div class="col-12 md:col-6">
          <label for="edit_interest" class="block mb-2">Interest Rate (%)</label>
          <InputNumber
            id="edit_interest"
            v-model="editForm.interest_rate"
            class="w-full"
            :min="0"
            :max="100"
            :maxFractionDigits="2"
          />
        </div>
        <div class="col-12 md:col-6">
          <label for="edit_interval" class="block mb-2">Repayment Interval</label>
          <Dropdown
            id="edit_interval"
            v-model="editForm.repayment_interval"
            :options="repaymentIntervals"
            optionLabel="label"
            optionValue="value"
            class="w-full"
          />
        </div>
        <div class="col-12">
          <label for="edit_notes" class="block mb-2">Notes</label>
          <Textarea id="edit_notes" v-model="editForm.notes" rows="3" class="w-full" />
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" text @click="editMode = false" />
        <Button
          label="Save Changes"
          icon="pi pi-check"
          severity="primary"
          @click="saveInvestmentChanges"
          :loading="saving"
        />
      </template>
    </Dialog>

    <!-- Mark Repayment as Paid Dialog -->
    <Dialog
      v-model:visible="markPaidDialog"
      :style="{ width: '500px' }"
      header="Mark Repayment as Paid"
      :modal="true"
    >
      <div v-if="selectedRepayment" class="grid">
        <div class="col-12">
          <div class="mb-3">
            <div class="text-sm text-gray-500">Payment Amount</div>
            <div class="text-2xl font-bold text-primary">{{ formatCurrency(selectedRepayment.amount) }}</div>
          </div>
          <div class="mb-3">
            <div class="text-sm text-gray-500">Due Date</div>
            <div class="font-semibold">{{ formatDate(selectedRepayment.due_date) }}</div>
          </div>
        </div>
        <div class="col-12">
          <label for="payment_method" class="block mb-2">Payment Method</label>
          <Dropdown
            id="payment_method"
            v-model="markPaidForm.payment_method"
            :options="paymentMethods"
            optionLabel="label"
            optionValue="value"
            placeholder="Select payment method"
            class="w-full"
          />
        </div>
        <div class="col-12">
          <label for="payment_reference" class="block mb-2">Payment Reference</label>
          <InputText
            id="payment_reference"
            v-model="markPaidForm.payment_reference"
            class="w-full"
            placeholder="Transaction ID, check number, etc."
          />
        </div>
        <div class="col-12">
          <label for="payment_notes" class="block mb-2">Notes (optional)</label>
          <Textarea id="payment_notes" v-model="markPaidForm.notes" rows="2" class="w-full" />
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" text @click="markPaidDialog = false" />
        <Button
          label="Mark as Paid"
          icon="pi pi-check"
          severity="success"
          @click="confirmMarkAsPaid"
          :loading="markingPaid"
        />
      </template>
    </Dialog>

    <!-- Cancel Investment Dialog -->
    <Dialog
      v-model:visible="cancelDialog"
      :style="{ width: '450px' }"
      header="Cancel Investment"
      :modal="true"
    >
      <div class="confirmation-content">
        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem; color: var(--orange-500)" />
        <span v-if="investment">
          Are you sure you want to cancel this investment of
          <b>{{ formatCurrency(investment.amount) }}</b>?
          This action cannot be undone.
        </span>
      </div>
      <template #footer>
        <Button label="Keep Investment" icon="pi pi-times" text @click="cancelDialog = false" />
        <Button
          label="Cancel Investment"
          icon="pi pi-trash"
          severity="danger"
          @click="confirmCancelInvestment"
          :loading="cancelling"
        />
      </template>
    </Dialog>

    <!-- Send Notification Dialog -->
    <Dialog
      v-model:visible="notificationDialog"
      :style="{ width: '600px' }"
      header="Send Notification"
      :modal="true"
    >
      <div class="grid">
        <div class="col-12">
          <label for="notif_subject" class="block mb-2">Subject</label>
          <InputText
            id="notif_subject"
            v-model="notificationForm.subject"
            class="w-full"
            placeholder="Notification subject"
          />
        </div>
        <div class="col-12">
          <label for="notif_message" class="block mb-2">Message</label>
          <Textarea
            id="notif_message"
            v-model="notificationForm.message"
            rows="5"
            class="w-full"
            placeholder="Enter notification message"
          />
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" text @click="notificationDialog = false" />
        <Button
          label="Send Notification"
          icon="pi pi-send"
          severity="primary"
          @click="sendNotification"
          :loading="sendingNotification"
        />
      </template>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useInvestmentStore } from '@/stores/investment'
import { useRole } from '@/composables/useRole'
import { useToast } from 'primevue/usetoast'
import type { Investment } from '@/api/investment.service'
import repaymentService from '@/api/repayment.service'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import ProgressSpinner from 'primevue/progressspinner'
import ProgressBar from 'primevue/progressbar'
import Avatar from 'primevue/avatar'
import Divider from 'primevue/divider'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Timeline from 'primevue/timeline'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'

const router = useRouter()
const route = useRoute()
const investmentStore = useInvestmentStore()
const { isAdmin, isManager } = useRole()
const toast = useToast()

const investment = ref<Investment | null>(null)
const loading = ref(false)
const editMode = ref(false)
const verifyDialog = ref(false)
const verifying = ref(false)
const showRepayments = ref(true)
const markPaidDialog = ref(false)
const selectedRepayment = ref<any>(null)
const markingPaid = ref(false)
const cancelDialog = ref(false)
const cancelling = ref(false)
const notificationDialog = ref(false)
const sendingNotification = ref(false)
const saving = ref(false)
const generatingPdf = ref(false)

const editForm = ref({
  amount: 0,
  duration_months: 0,
  interest_rate: 0,
  repayment_interval: 'monthly',
  notes: '',
})

const markPaidForm = ref({
  payment_method: '',
  payment_reference: '',
  notes: '',
})

const notificationForm = ref({
  subject: '',
  message: '',
})

const repaymentIntervals = [
  { label: 'Monthly', value: 'monthly' },
  { label: 'Quarterly', value: 'quarterly' },
  { label: 'Annually', value: 'annually' },
]

const paymentMethods = [
  { label: 'Bank Transfer', value: 'bank_transfer' },
  { label: 'Cash', value: 'cash' },
  { label: 'Check', value: 'check' },
  { label: 'Direct Debit', value: 'direct_debit' },
  { label: 'Other', value: 'other' },
]

const completionPercentage = computed(() => {
  if (!investment.value || !investment.value.total_repayment) return 0
  return Math.min((investment.value.paid_amount / investment.value.total_repayment) * 100, 100)
})

const remainingBalance = computed(() => {
  if (!investment.value) return 0
  return (investment.value.total_repayment || 0) - investment.value.paid_amount
})

const activityLog = computed(() => {
  const log: any[] = []

  if (investment.value) {
    log.push({
      title: 'Investment Created',
      description: 'Investment was created and submitted',
      timestamp: formatDate(investment.value.created_at),
      date: new Date(investment.value.created_at),
    })

    if (investment.value.verified) {
      log.push({
        title: 'Investment Verified',
        description: `Verified by ${investment.value.verified_by_user?.name || 'Admin'}`,
        timestamp: formatDate(investment.value.verified_at || ''),
        date: new Date(investment.value.verified_at || ''),
      })
    }

    if (investment.value.start_date) {
      log.push({
        title: 'Investment Started',
        description: 'Investment period began',
        timestamp: formatDate(investment.value.start_date),
        date: new Date(investment.value.start_date),
      })
    }
  }

  // Sort by date ascending (oldest first)
  return log.sort((a, b) => a.date.getTime() - b.date.getTime())
})

onMounted(async () => {
  await fetchInvestment()
})

async function fetchInvestment() {
  loading.value = true
  try {
    const investmentId = route.params.id as string
    await investmentStore.fetchInvestment(investmentId)
    investment.value = investmentStore.currentInvestment
  } catch (error) {
    console.error('Error fetching investment:', error)
  } finally {
    loading.value = false
  }
}

function handleVerify() {
  verifyDialog.value = true
}

async function verifyInvestment() {
  if (!investment.value) return

  verifying.value = true
  try {
    await investmentStore.verifyInvestment(investment.value.id)
    verifyDialog.value = false
    await fetchInvestment()
  } catch (error) {
    console.error('Error verifying investment:', error)
  } finally {
    verifying.value = false
  }
}

function viewUserProfile(userId: number) {
  router.push({ name: 'AdminUserDetail', params: { id: userId } })
}

function getStatusSeverity(status: string): string {
  const severityMap: Record<string, string> = {
    draft: 'secondary',
    active: 'info',
    funded: 'success',
    operational: 'success',
    completed: 'secondary',
    cancelled: 'danger',
    pending: 'warning',
    verified: 'info',
  }
  return severityMap[status] || 'info'
}

function getRepaymentStatusSeverity(status: string): string {
  const severityMap: Record<string, string> = {
    pending: 'warning',
    paid: 'success',
    overdue: 'danger',
    cancelled: 'secondary',
  }
  return severityMap[status] || 'info'
}

function formatCurrency(value: number | string): string {
  const numValue = typeof value === 'string' ? parseFloat(value) : value
  return new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR',
  }).format(numValue)
}

function formatDate(date: string): string {
  if (!date) return ''
  return new Date(date).toLocaleDateString('de-DE', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

function capitalizeFirst(str: string): string {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

function getInitials(name: string): string {
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

// Watch for editMode changes to populate form
watch(editMode, (newVal) => {
  if (newVal && investment.value) {
    editForm.value = {
      amount: investment.value.amount,
      duration_months: investment.value.duration_months,
      interest_rate: investment.value.interest_rate,
      repayment_interval: investment.value.repayment_interval,
      notes: investment.value.notes || '',
    }
  }
})

async function saveInvestmentChanges() {
  if (!investment.value) return

  saving.value = true
  try {
    await investmentStore.updateInvestment(investment.value.id, editForm.value)
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Investment updated successfully',
      life: 3000,
    })
    editMode.value = false
    await fetchInvestment()
  } catch (error) {
    console.error('Error updating investment:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to update investment',
      life: 3000,
    })
  } finally {
    saving.value = false
  }
}

function handleMarkRepaymentAsPaid(repayment: any) {
  selectedRepayment.value = repayment
  markPaidForm.value = {
    payment_method: '',
    payment_reference: '',
    notes: '',
  }
  markPaidDialog.value = true
}

async function confirmMarkAsPaid() {
  if (!selectedRepayment.value) return

  markingPaid.value = true
  try {
    await repaymentService.markAsPaid(selectedRepayment.value.id, {
      ...markPaidForm.value,
      amount: selectedRepayment.value.amount, // Backend requires amount field
    })
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Repayment marked as paid',
      life: 3000,
    })
    markPaidDialog.value = false
    await fetchInvestment()
  } catch (error) {
    console.error('Error marking repayment as paid:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to mark repayment as paid',
      life: 3000,
    })
  } finally {
    markingPaid.value = false
  }
}

async function handleGenerateContract() {
  if (!investment.value) return

  generatingPdf.value = true
  try {
    const { apiClient } = await import('@/api')

    // Fetch PDF as blob from backend
    const response = await apiClient.get(
      `/api/v1/pdf/investments/${investment.value.id}/contract`,
      {
        responseType: 'blob',
      }
    )

    // Create blob URL from response data (axios already returns blob with responseType: 'blob')
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)

    // Open in new tab
    const newWindow = window.open(url, '_blank')

    // Clean up the URL after window loads
    if (newWindow) {
      newWindow.onload = () => {
        setTimeout(() => window.URL.revokeObjectURL(url), 100)
      }
    }

    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Contract PDF generated successfully',
      life: 3000,
    })
  } catch (error) {
    console.error('Error generating contract:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to generate contract',
      life: 3000,
    })
  } finally {
    generatingPdf.value = false
  }
}

function handleSendEmail(email: string) {
  // Open default email client
  window.location.href = `mailto:${email}?subject=Regarding Your Investment #${investment.value?.id.substring(0, 8)}`
}

async function sendNotification() {
  if (!investment.value || !notificationForm.value.subject || !notificationForm.value.message) {
    toast.add({
      severity: 'warn',
      summary: 'Warning',
      detail: 'Please fill in subject and message',
      life: 3000,
    })
    return
  }

  sendingNotification.value = true
  try {
    const { apiClient } = await import('@/api')
    await apiClient.post('/api/v1/notifications/send', {
      user_id: investment.value.user_id,
      title: notificationForm.value.subject,
      message: notificationForm.value.message,
      type: 'info',
      category: 'system',
    })

    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Notification sent successfully',
      life: 3000,
    })
    notificationDialog.value = false
    notificationForm.value = { subject: '', message: '' }
  } catch (error) {
    console.error('Error sending notification:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to send notification',
      life: 3000,
    })
  } finally {
    sendingNotification.value = false
  }
}

async function handleDownloadDocuments() {
  if (!investment.value) {
    return
  }

  try {
    const { apiClient } = await import('@/api')

    // Make request to bulk download endpoint
    const response = await apiClient.post(
      '/api/v1/files/bulk-download',
      {
        container_type: 'investment',
        container_id: investment.value.id,
      },
      {
        responseType: 'blob', // Important for file downloads
      }
    )

    // Create a download link
    const url = window.URL.createObjectURL(new Blob([response]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `investment_${investment.value.id.substring(0, 8)}_documents.zip`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Documents downloaded successfully',
      life: 3000,
    })
  } catch (error: any) {
    console.error('Error downloading documents:', error)

    // Check if it's a 404 error
    if (error.response?.status === 404) {
      toast.add({
        severity: 'info',
        summary: 'No Documents',
        detail: 'No documents found for this investment',
        life: 3000,
      })
    } else {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to download documents',
        life: 3000,
      })
    }
  }
}

async function confirmCancelInvestment() {
  if (!investment.value) return

  cancelling.value = true
  try {
    await investmentStore.deleteInvestment(investment.value.id)
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Investment cancelled successfully',
      life: 3000,
    })
    router.push({ name: 'AdminInvestmentList' })
  } catch (error) {
    console.error('Error cancelling investment:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to cancel investment',
      life: 3000,
    })
  } finally {
    cancelling.value = false
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/views/_admin-users';

.field label {
  display: block;
  margin-bottom: 0.25rem;
}

.confirmation-content {
  display: flex;
  align-items: center;
  padding: 1rem;
}

.repayment-schedule-container {
  :deep(.p-datatable-wrapper) {
    border-radius: 6px;

    /* Custom scrollbar styling */
    &::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    &::-webkit-scrollbar-track {
      background: var(--surface-100);
      border-radius: 4px;
    }

    &::-webkit-scrollbar-thumb {
      background: var(--primary-300);
      border-radius: 4px;
      transition: background 0.2s;

      &:hover {
        background: var(--primary-400);
      }
    }

    /* Firefox scrollbar */
    scrollbar-width: thin;
    scrollbar-color: var(--primary-300) var(--surface-100);
  }

  :deep(.p-datatable-thead) {
    position: sticky;
    top: 0;
    z-index: 1;
  }
}
</style>
