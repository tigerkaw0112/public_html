pipeline {
    agent any

    triggers {
        // Poll SCM as fallback if webhook fails
        pollSCM('H/2 * * * *')
    }

    environment {
        // Build Information
        BUILD_TAG = "${env.BUILD_NUMBER}"
        // เช็กว่า Jenkins มี git ติดตั้งหรือยัง ถ้า error บรรทัดนี้ให้ comment ออกได้
        GIT_COMMIT_SHORT = sh(returnStdout: true, script: 'git rev-parse --short HEAD').trim()
    }

    parameters {
        booleanParam(
            name: 'CLEAN_VOLUMES',
            defaultValue: false, // ⚠️ เปลี่ยนเป็น false เพื่อไม่ให้ข้อมูลหายทุกครั้งที่ Build
            description: 'Remove volumes (clears database)'
        )
    }

    stages {
        stage('Checkout') {
            steps {
                script {
                    echo "Checking out code..."
                    checkout scm
                    echo "Deploying to production environment"
                    echo "Build: ${BUILD_TAG}, Commit: ${GIT_COMMIT_SHORT}"
                }
            }
        }

        stage('Validate') {
            steps {
                script {
                    echo "Validating Docker Compose configuration..."
                    // ตรวจสอบว่า docker compose ทำงานได้ปกติ
                    sh 'docker compose config'
                }
            }
        }

        stage('Prepare Environment') {
            steps {
                script {
                    echo "Preparing environment configuration..."

                    // Load credentials from Jenkins (ต้องไปตั้งค่า Credential ID นี้ใน Jenkins ก่อน)
                    // ถ้ายังไม่มี ให้ hardcode ไปก่อนชั่วคราว หรือสร้าง Credential ชื่อ 'MYSQL_ROOT_PASSWORD' และ 'MYSQL_PASSWORD'
                    withCredentials([
                        string(credentialsId: 'MYSQL_ROOT_PASSWORD', variable: 'MYSQL_ROOT_PASS'),
                        string(credentialsId: 'MYSQL_PASSWORD', variable: 'MYSQL_PASS')
                    ]) {
                        // Create .env file
                        sh """
                            cat > .env <<EOF
MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASS}
MYSQL_DATABASE=4509882_tigerlion
MYSQL_USER=tigerlion
MYSQL_PASSWORD=${MYSQL_PASS}
MYSQL_PORT=8888
PHPMYADMIN_PORT=8888
PHP_PORT=3000
DB_PORT=3306
EOF
                        """
                    }

                    echo "Environment configuration created"
                }
            }
        }

        stage('Deploy') {
            steps {
                script {
                    echo "Deploying to production using Docker Compose..."

                    // Stop and remove existing containers (force)
                    def downCommand = 'docker compose down'
                    if (params.CLEAN_VOLUMES) {
                        echo "WARNING: Removing volumes (database will be cleared)"
                        downCommand = 'docker compose down -v'
                    }
                    
                    // Stop containers and remove orphaned containers
                    sh """
                        # Stop current project containers
                        ${downCommand} || true
                        docker compose down --remove-orphans || true
                        
                        # Force remove any containers with our project names
                        docker rm -f public_html_php public_html_mysql public_html_phpmyadmin 2>/dev/null || true
                        
                        # Remove any containers using our ports (3000, 8080, 8888)
                        docker ps --filter "publish=3000" -q | xargs docker rm -f 2>/dev/null || true
                        docker ps --filter "publish=8080" -q | xargs docker rm -f 2>/dev/null || true
                        docker ps --filter "publish=8888" -q | xargs docker rm -f 2>/dev/null || true
                        
                        # Remove stopped containers
                        docker container prune -f || true
                        
                        # Wait a moment for ports to be released
                        sleep 2
                    """

                    // Build and start services
                    sh """
                        docker compose build --no-cache
                        docker compose up -d
                    """

                    echo "Deployment completed"
                }
            }
        }

        stage('Health Check') {
            steps {
                script {
                    echo "Waiting for services to start..."
                    sh 'sleep 15'

                    echo "Performing health check..."

                    sh """
                        # Check if containers are running
                        docker compose ps

                        # Wait for PHP/Apache to be ready (max 60 seconds)
                        timeout 60 bash -c 'until curl -f http://localhost:3000/; do sleep 2; done' || exit 1

                        # Check if index.php is accessible
                        curl -f http://localhost:3000/index.php || exit 1

                        echo "Health check passed!"
                    """
                }
            }
        }

        stage('Verify Deployment') {
            steps {
                script {
                    echo "Verifying all services..."

                    sh """
                        echo "=== Container Status ==="
                        docker compose ps

                        echo ""
                        echo "=== Service Logs (last 20 lines) ==="
                        docker compose logs --tail=20

                        echo ""
                        echo "=== Deployed Services ==="
                        echo "PHP Application (Frontend): http://localhost:3000"
                        echo "phpMyAdmin (Database): http://localhost:8888"
                        echo "MySQL (Data): localhost:8888"
                        echo "Jenkins: http://localhost:8080"
                    """
                }
            }
        }
    }

    post {
        success {
            echo "✅ Deployment completed successfully!"
            echo "Access your application:"
            echo "  - PHP Application (Frontend): http://localhost:3000"
            echo "  - phpMyAdmin (Database): http://localhost:8888"
            echo "  - MySQL (Data): localhost:8888"
            echo "  - Jenkins: http://localhost:8080"
            echo "  - Student Page: http://localhost:3000/student/select_course.php"
            echo "  - Teacher Login: http://localhost:3000/teacher/login.php"
        }

        failure {
            echo "❌ Deployment failed!"
            script {
                echo "Printing container logs for debugging..."
                sh 'docker compose logs --tail=50 || true'
            }
        }

        always {
            echo "Cleaning up old Docker resources..."
            sh """
                docker image prune -f
                # อย่าลืมว่าถ้า prune container อาจทำให้ debug ยากถ้าลบไปเลย แต่เพื่อความสะอาดก็ควรทำ
                # docker container prune -f 
            """
        }
    }
}

