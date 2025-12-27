<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1400px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <h1 style="margin: 0; color: #2c3e50;">
            <i class="fas fa-chart-line"></i> Dashboard
        </h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">Overview of your projects and tasks</p>
    </div>

    <?php
    $data = $dashboardData;
    $totalProjects = $data['total_projects'];
    $tasksByStatus = $data['tasks_by_status'];
    $overdueCount = $data['overdue_tasks_count'];
    $overdueTasks = $data['overdue_tasks'];
    $tasksByAssignee = $data['tasks_by_assignee'];
    $progress = $data['progress'];
    ?>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Total Projects -->
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 8px;">Total Projects</div>
                    <div style="font-size: 2.5rem; font-weight: bold;"><?= $totalProjects ?></div>
                </div>
                <div style="font-size: 3rem; opacity: 0.3;">
                    <i class="fas fa-folder"></i>
                </div>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 8px;">Overdue Tasks</div>
                    <div style="font-size: 2.5rem; font-weight: bold;"><?= $overdueCount ?></div>
                </div>
                <div style="font-size: 3rem; opacity: 0.3;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <!-- Overall Progress -->
        <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 8px;">Overall Progress</div>
                    <div style="font-size: 2.5rem; font-weight: bold;"><?= $progress['overall'] ?>%</div>
                </div>
                <div style="font-size: 3rem; opacity: 0.3;">
                    <i class="fas fa-chart-pie"></i>
                </div>
            </div>
        </div>

        <!-- Total Tasks -->
        <?php
        $totalTasks = array_sum($tasksByStatus);
        ?>
        <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 8px;">Total Tasks</div>
                    <div style="font-size: 2.5rem; font-weight: bold;"><?= $totalTasks ?></div>
                </div>
                <div style="font-size: 3rem; opacity: 0.3;">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <!-- Tasks by Status -->
        <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.3rem; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                <i class="fas fa-columns"></i> Tasks by Status
            </h2>
            <?php if (empty($tasksByStatus)): ?>
                <p style="color: #666; text-align: center; padding: 20px;">No tasks found</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php
                    $statusColors = [
                        'Backlog' => '#e3f2fd',
                        'Todo' => '#f3e5f5',
                        'In Progress' => '#fff3e0',
                        'Review' => '#e8f5e9',
                        'Done' => '#e0f2f1',
                        'Testing' => '#fce4ec'
                    ];
                    foreach ($tasksByStatus as $status => $count):
                        $color = $statusColors[$status] ?? '#f5f5f5';
                        $percentage = $totalTasks > 0 ? round(($count / $totalTasks) * 100, 1) : 0;
                    ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                <span style="font-weight: 600; color: #2c3e50;"><?= esc($status) ?></span>
                                <span style="font-weight: bold; color: #666;"><?= $count ?> (<?= $percentage ?>%)</span>
                            </div>
                            <div style="height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; background: <?= $color ?>; width: <?= $percentage ?>%; transition: width 0.3s;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tasks by Assignee -->
        <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.3rem; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                <i class="fas fa-users"></i> Tasks by Assignee
            </h2>
            <?php if (empty($tasksByAssignee)): ?>
                <p style="color: #666; text-align: center; padding: 20px;">No assigned tasks found</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($tasksByAssignee as $assignee): ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                <span style="font-weight: 600; color: #2c3e50;">
                                    <i class="fas fa-user"></i> <?= esc($assignee['assignee_name']) ?>
                                </span>
                                <span style="font-weight: bold; color: #666;"><?= $assignee['count'] ?></span>
                            </div>
                            <div style="height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                                <?php
                                $maxCount = !empty($tasksByAssignee) ? max(array_column($tasksByAssignee, 'count')) : 1;
                                $percentage = $maxCount > 0 ? round(($assignee['count'] / $maxCount) * 100, 1) : 0;
                                ?>
                                <div style="height: 100%; background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%); width: <?= $percentage ?>%; transition: width 0.3s;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Progress by Project -->
    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 30px;">
        <h2 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.3rem; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
            <i class="fas fa-chart-bar"></i> Progress by Project
        </h2>
        <?php if (empty($progress['by_project'])): ?>
            <p style="color: #666; text-align: center; padding: 20px;">No projects with tasks found</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($progress['by_project'] as $projectProgress): ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div>
                                <a href="/projects/<?= $projectProgress['project_id'] ?>" style="font-weight: 600; color: #2c3e50; text-decoration: none; font-size: 1.1rem;">
                                    <?= esc($projectProgress['project_name']) ?>
                                    <?php if (isset($projectProgress['project_key']) && $projectProgress['project_key']): ?>
                                        <code style="background: #f1f3f4; padding: 2px 6px; border-radius: 3px; font-size: 0.85rem; margin-left: 8px; color: #5f6368;">
                                            <?= esc($projectProgress['project_key']) ?>
                                        </code>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: bold; color: #2c3e50; font-size: 1.2rem;">
                                    <?= $projectProgress['progress'] ?>%
                                </div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <?= $projectProgress['completed_tasks'] ?> / <?= $projectProgress['total_tasks'] ?> tasks
                                </div>
                            </div>
                        </div>
                        <div style="height: 12px; background: #f0f0f0; border-radius: 6px; overflow: hidden; position: relative;">
                            <div style="height: 100%; background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%); width: <?= $projectProgress['progress'] ?>%; transition: width 0.3s;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Overdue Tasks -->
    <?php if ($overdueCount > 0): ?>
        <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <h2 style="margin: 0 0 20px 0; color: #d32f2f; font-size: 1.3rem; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                <i class="fas fa-exclamation-triangle"></i> Overdue Tasks (<?= $overdueCount ?>)
            </h2>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($overdueTasks as $task): ?>
                    <div style="padding: 15px; background: #fff3cd; border-left: 4px solid #d32f2f; border-radius: 4px;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                    <a href="/issues/<?= $task['id'] ?>" style="font-weight: 600; color: #2c3e50; text-decoration: none;">
                                        <?= esc($task['title']) ?>
                                    </a>
                                    <?php if ($task['issue_key']): ?>
                                        <code style="background: #f1f3f4; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; color: #5f6368;">
                                            <?= esc($task['issue_key']) ?>
                                        </code>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <span>
                                        <i class="fas fa-folder"></i> 
                                        <a href="/projects/<?= $task['project_id'] ?>" style="color: #1a73e8; text-decoration: none;">
                                            <?= esc($task['project_name']) ?>
                                        </a>
                                    </span>
                                    <?php if ($task['assignee_name']): ?>
                                        <span style="margin-left: 15px;">
                                            <i class="fas fa-user"></i> <?= esc($task['assignee_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($task['column_name']): ?>
                                        <span style="margin-left: 15px;">
                                            <i class="fas fa-columns"></i> <?= esc($task['column_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span style="margin-left: 15px; color: #d32f2f; font-weight: 600;">
                                        <i class="fas fa-calendar-times"></i> Due: <?= date('M d, Y', strtotime($task['due_date'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($overdueCount > count($overdueTasks)): ?>
                    <div style="text-align: center; padding: 15px; color: #666; font-size: 0.9rem;">
                        <i class="fas fa-ellipsis-h"></i> Showing <?= count($overdueTasks) ?> of <?= $overdueCount ?> overdue tasks
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
