<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1400px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects/<?= $project['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
        <h1 style="margin: 0; color: #2c3e50;">
            <i class="fas fa-chart-bar"></i> Reports - <?= esc($project['name']) ?>
        </h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">Analytics and metrics for your project</p>
    </div>

    <!-- Tabs Navigation -->
    <div style="background: #fff; border-radius: 8px 8px 0 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 0; border-bottom: 2px solid #e0e0e0; margin-bottom: 0;">
        <div style="display: flex; gap: 0;">
            <button onclick="showTab('velocity')" class="report-tab active" id="tab-velocity">
                <i class="fas fa-rocket"></i> Velocity
            </button>
            <button onclick="showTab('burndown')" class="report-tab" id="tab-burndown">
                <i class="fas fa-chart-line"></i> Burndown
            </button>
            <button onclick="showTab('burnup')" class="report-tab" id="tab-burnup">
                <i class="fas fa-chart-area"></i> Burnup
            </button>
            <button onclick="showTab('leadtime')" class="report-tab" id="tab-leadtime">
                <i class="fas fa-clock"></i> Lead & Cycle Time
            </button>
            <button onclick="showTab('productivity')" class="report-tab" id="tab-productivity">
                <i class="fas fa-users"></i> Productivity
            </button>
        </div>
    </div>

    <!-- Velocity Chart Tab -->
    <div id="tab-content-velocity" class="tab-content active">
        <div class="card" style="background: #fff; border-radius: 0 0 8px 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50;">Velocity Chart</h2>
            <p style="color: #666; margin-bottom: 20px;">Story points completed per sprint</p>
            <canvas id="velocityChart" style="max-height: 400px;"></canvas>
            <div id="velocity-stats" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;"></div>
        </div>
    </div>

    <!-- Burndown Chart Tab -->
    <div id="tab-content-burndown" class="tab-content">
        <div class="card" style="background: #fff; border-radius: 0 0 8px 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50;">Burndown Chart</h2>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Select Sprint:</label>
                <select id="burndown-sprint" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; min-width: 300px;" onchange="loadBurndownChart()">
                    <option value="">Select a sprint...</option>
                    <?php foreach ($sprints as $sprint): ?>
                        <option value="<?= $sprint['id'] ?>">
                            <?= esc($sprint['name']) ?> (<?= date('M d', strtotime($sprint['start_date'])) ?> - <?= date('M d', strtotime($sprint['end_date'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <canvas id="burndownChart" style="max-height: 400px;"></canvas>
            <div id="burndown-stats" style="margin-top: 20px;"></div>
        </div>
    </div>

    <!-- Burnup Chart Tab -->
    <div id="tab-content-burnup" class="tab-content">
        <div class="card" style="background: #fff; border-radius: 0 0 8px 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50;">Burnup Chart</h2>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Select Sprint:</label>
                <select id="burnup-sprint" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; min-width: 300px;" onchange="loadBurnupChart()">
                    <option value="">Select a sprint...</option>
                    <?php foreach ($sprints as $sprint): ?>
                        <option value="<?= $sprint['id'] ?>">
                            <?= esc($sprint['name']) ?> (<?= date('M d', strtotime($sprint['start_date'])) ?> - <?= date('M d', strtotime($sprint['end_date'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <canvas id="burnupChart" style="max-height: 400px;"></canvas>
            <div id="burnup-stats" style="margin-top: 20px;"></div>
        </div>
    </div>

    <!-- Lead Time & Cycle Time Tab -->
    <div id="tab-content-leadtime" class="tab-content">
        <div class="card" style="background: #fff; border-radius: 0 0 8px 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50;">Lead Time & Cycle Time</h2>
            <div style="margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Start Date:</label>
                    <input type="date" id="leadtime-start" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" onchange="loadLeadTime()">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">End Date:</label>
                    <input type="date" id="leadtime-end" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" onchange="loadLeadTime()">
                </div>
            </div>
            <div id="leadtime-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;"></div>
            <div id="leadtime-table" style="overflow-x: auto;"></div>
        </div>
    </div>

    <!-- Productivity Tab -->
    <div id="tab-content-productivity" class="tab-content">
        <div class="card" style="background: #fff; border-radius: 0 0 8px 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50;">Productivity per User</h2>
            <div style="margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Start Date:</label>
                    <input type="date" id="productivity-start" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" onchange="loadProductivity()">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">End Date:</label>
                    <input type="date" id="productivity-end" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" onchange="loadProductivity()">
                </div>
            </div>
            <canvas id="productivityChart" style="max-height: 400px;"></canvas>
            <div id="productivity-table" style="margin-top: 30px; overflow-x: auto;"></div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .report-tab {
        padding: 15px 25px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 600;
        color: #666;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .report-tab:hover {
        background: #f8f9fa;
        color: #2c3e50;
    }

    .report-tab.active {
        color: #4a90e2;
        border-bottom-color: #4a90e2;
        background: #f8f9fa;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }
</style>

<script>
    const projectId = <?= $project['id'] ?>;
    let velocityChart = null;
    let burndownChart = null;
    let burnupChart = null;
    let productivityChart = null;

    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.report-tab').forEach(btn => btn.classList.remove('active'));

        // Show selected tab
        document.getElementById('tab-content-' + tabName).classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');

        // Load data when tab is shown
        if (tabName === 'velocity') {
            loadVelocityChart();
        } else if (tabName === 'leadtime') {
            loadLeadTime();
        } else if (tabName === 'productivity') {
            loadProductivity();
        }
    }

    // Load Velocity Chart
    function loadVelocityChart() {
        fetch(`/projects/${projectId}/reports/velocity`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderVelocityChart(data.data);
                }
            })
            .catch(err => console.error('Error loading velocity chart:', err));
    }

    function renderVelocityChart(data) {
        const ctx = document.getElementById('velocityChart');
        if (velocityChart) {
            velocityChart.destroy();
        }

        const labels = data.chart_data.map(d => d.sprint_name);
        const velocities = data.chart_data.map(d => d.velocity);
        const avgVelocity = data.average_velocity;

        velocityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Velocity (Story Points)',
                    data: velocities,
                    backgroundColor: '#4a90e2',
                    borderColor: '#3a7bc8',
                    borderWidth: 1
                }, {
                    label: 'Average Velocity',
                    data: new Array(labels.length).fill(avgVelocity),
                    type: 'line',
                    borderColor: '#f5576c',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Story Points'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Sprint'
                        }
                    }
                }
            }
        });

        // Display stats
        document.getElementById('velocity-stats').innerHTML = `
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 0.85rem; color: #666; margin-bottom: 5px;">Average Velocity</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">${avgVelocity}</div>
                <div style="font-size: 0.85rem; color: #666; margin-top: 5px;">Story Points</div>
            </div>
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 0.85rem; color: #666; margin-bottom: 5px;">Sprints Analyzed</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">${data.chart_data.length}</div>
            </div>
        `;
    }

    // Load Burndown Chart
    function loadBurndownChart() {
        const sprintId = document.getElementById('burndown-sprint').value;
        if (!sprintId) return;

        fetch(`/projects/${projectId}/reports/burndown/${sprintId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderBurndownChart(data.data);
                }
            })
            .catch(err => console.error('Error loading burndown chart:', err));
    }

    function renderBurndownChart(data) {
        const ctx = document.getElementById('burndownChart');
        if (burndownChart) {
            burndownChart.destroy();
        }

        const labels = data.chart_data.map(d => d.date);
        const remaining = data.chart_data.map(d => d.remaining);
        const ideal = data.chart_data.map(d => d.ideal);

        burndownChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Remaining Work',
                    data: remaining,
                    borderColor: '#4a90e2',
                    backgroundColor: 'rgba(74, 144, 226, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Ideal Burndown',
                    data: ideal,
                    borderColor: '#999',
                    borderDash: [5, 5],
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Story Points Remaining'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });

        document.getElementById('burndown-stats').innerHTML = `
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <strong>Sprint:</strong> ${data.sprint.name}<br>
                <strong>Total Story Points:</strong> ${data.total_points}
            </div>
        `;
    }

    // Load Burnup Chart
    function loadBurnupChart() {
        const sprintId = document.getElementById('burnup-sprint').value;
        if (!sprintId) return;

        fetch(`/projects/${projectId}/reports/burnup/${sprintId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderBurnupChart(data.data);
                }
            })
            .catch(err => console.error('Error loading burnup chart:', err));
    }

    function renderBurnupChart(data) {
        const ctx = document.getElementById('burnupChart');
        if (burnupChart) {
            burnupChart.destroy();
        }

        const labels = data.chart_data.map(d => d.date);
        const completed = data.chart_data.map(d => d.completed);
        const scope = data.chart_data.map(d => d.scope);

        burnupChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Completed Work',
                    data: completed,
                    borderColor: '#43e97b',
                    backgroundColor: 'rgba(67, 233, 123, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Total Scope',
                    data: scope,
                    borderColor: '#999',
                    borderDash: [5, 5],
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Story Points'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });

        document.getElementById('burnup-stats').innerHTML = `
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <strong>Sprint:</strong> ${data.sprint.name}<br>
                <strong>Total Story Points:</strong> ${data.total_points}
            </div>
        `;
    }

    // Load Lead Time
    function loadLeadTime() {
        const startDate = document.getElementById('leadtime-start').value;
        const endDate = document.getElementById('leadtime-end').value;
        
        let url = `/projects/${projectId}/reports/lead-time`;
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (params.toString()) url += '?' + params.toString();

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderLeadTime(data.data);
                }
            })
            .catch(err => console.error('Error loading lead time:', err));
    }

    function renderLeadTime(data) {
        document.getElementById('leadtime-stats').innerHTML = `
            <div style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px;">
                <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 8px;">Average Lead Time</div>
                <div style="font-size: 2rem; font-weight: bold;">${data.average_lead_time} days</div>
            </div>
            <div style="padding: 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 8px;">
                <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 8px;">Average Cycle Time</div>
                <div style="font-size: 2rem; font-weight: bold;">${data.average_cycle_time} days</div>
            </div>
            <div style="padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 0.9rem; color: #666; margin-bottom: 8px;">Median Lead Time</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">${data.median_lead_time} days</div>
            </div>
            <div style="padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 0.9rem; color: #666; margin-bottom: 8px;">Median Cycle Time</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">${data.median_cycle_time} days</div>
            </div>
            <div style="padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 0.9rem; color: #666; margin-bottom: 8px;">Total Issues</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">${data.total_issues}</div>
            </div>
        `;

        // Render table
        let tableHTML = '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;"><thead><tr style="background: #f8f9fa;"><th style="padding: 12px; text-align: left; border-bottom: 2px solid #e0e0e0;">Issue</th><th style="padding: 12px; text-align: left; border-bottom: 2px solid #e0e0e0;">Lead Time</th><th style="padding: 12px; text-align: left; border-bottom: 2px solid #e0e0e0;">Cycle Time</th><th style="padding: 12px; text-align: left; border-bottom: 2px solid #e0e0e0;">Completed</th></tr></thead><tbody>';
        data.issues.slice(0, 20).forEach(issue => {
            tableHTML += `<tr style="border-bottom: 1px solid #e0e0e0;"><td style="padding: 12px;"><a href="/issues/${issue.issue_id}" style="color: #1a73e8; text-decoration: none;">${issue.issue_key} - ${issue.title}</a></td><td style="padding: 12px;">${issue.lead_time} days</td><td style="padding: 12px;">${issue.cycle_time} days</td><td style="padding: 12px;">${new Date(issue.completed_at).toLocaleDateString()}</td></tr>`;
        });
        tableHTML += '</tbody></table>';
        document.getElementById('leadtime-table').innerHTML = tableHTML;
    }

    // Load Productivity
    function loadProductivity() {
        const startDate = document.getElementById('productivity-start').value;
        const endDate = document.getElementById('productivity-end').value;
        
        let url = `/projects/${projectId}/reports/productivity`;
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (params.toString()) url += '?' + params.toString();

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderProductivity(data.data);
                }
            })
            .catch(err => console.error('Error loading productivity:', err));
    }

    function renderProductivity(data) {
        const ctx = document.getElementById('productivityChart');
        if (productivityChart) {
            productivityChart.destroy();
        }

        const labels = data.users.map(u => u.user_name);
        const storyPoints = data.users.map(u => u.completed_story_points);
        const issues = data.users.map(u => u.completed_issues);

        productivityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Completed Story Points',
                    data: storyPoints,
                    backgroundColor: '#4a90e2',
                    yAxisID: 'y'
                }, {
                    label: 'Completed Issues',
                    data: issues,
                    backgroundColor: '#43e97b',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Story Points'
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Issues'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Render table
        let tableHTML = '<table style="width: 100%; border-collapse: collapse;"><thead><tr style="background: #f8f9fa;"><th style="padding: 12px; text-align: left; border-bottom: 2px solid #e0e0e0;">User</th><th style="padding: 12px; text-align: right; border-bottom: 2px solid #e0e0e0;">Issues</th><th style="padding: 12px; text-align: right; border-bottom: 2px solid #e0e0e0;">Story Points</th></tr></thead><tbody>';
        data.users.forEach(user => {
            tableHTML += `<tr style="border-bottom: 1px solid #e0e0e0;"><td style="padding: 12px;">${user.user_name}</td><td style="padding: 12px; text-align: right;">${user.completed_issues}</td><td style="padding: 12px; text-align: right;">${user.completed_story_points}</td></tr>`;
        });
        tableHTML += '</tbody></table>';
        document.getElementById('productivity-table').innerHTML = tableHTML;
    }

    // Load initial data
    document.addEventListener('DOMContentLoaded', function() {
        loadVelocityChart();
    });
</script>

<?= $this->endSection() ?>
