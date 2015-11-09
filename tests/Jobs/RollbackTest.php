<?php namespace Tests\Jobs;

use App\Jobs\Rollback;

use Tests\Helpers\Factory;

class RollbackTest extends \TestCase {

	use \Tests\Helpers\MockeryHelper;

	protected $mockDeploymentRepository;

	protected $mockProjectRepository;

	protected $mockServerRepository;

	protected $mockProcessBuilder;

	protected $mockProcess;

	protected $mockDeployerFileDirector;

	protected $mockServerListFileBuilder;

	protected $mockRecipeFileBuilder;

	protected $mockDeploymentFileBuilder;

	public function setUp()
	{
		parent::setUp();

		$this->mockDeploymentRepository = $this->mock('App\Repositories\Deployment\DeploymentInterface');
		$this->mockProjectRepository = $this->mock('App\Repositories\Project\ProjectInterface');
		$this->mockServerRepository = $this->mock('App\Repositories\Server\ServerInterface');
		$this->mockProcessBuilder = $this->mock('Symfony\Component\Process\ProcessBuilder');
		$this->mockProcess = $this->mockPartial('Symfony\Component\Process\Process');
		$this->mockDeployerFileDirector = $this->mock('App\Services\Deployment\DeployerFileDirector');
		$this->mockServerListFileBuilder = $this->mock('App\Services\Deployment\DeployerServerListFileBuilder');
		$this->mockRecipeFileBuilder = $this->mock('App\Services\DeploymentInterface\DeployerRecipeFileBuilder');
		$this->mockDeploymentFileBuilder = $this->mock('App\Services\Deployment\DeployerDeploymentFileBuilder');
	}

	public function test_Should_Work_When_DeployerIsNormalEnd()
	{
		$deployment = Factory::build('App\Models\Deployment', [
			'id'         => 1,
			'project_id' => 1,
			'number'     => 1,
			'task'       => 'deploy',
			'user_id'    => 1,
			'created_at' => new \Carbon\Carbon,
			'updated_at' => new \Carbon\Carbon,
			'user'       => new \App\Models\User,
		]);

		$recipe = factory::build('app\models\recipe', [
			'id'          => 1,
			'name'        => 'recipe 1',
			'desctiption' => '',
			'body'        => '',
		]);

		$project = Factory::build('App\Models\Project', [
			'id'         => 1,
			'name'       => 'Project 1',
			'recipe_id'  => 1,
			'stage'      => 'staging',
			'created_at' => new \Carbon\Carbon,
			'updated_at' => new \Carbon\Carbon,
			'recipes'    => [$recipe],
		]);

		$this->mockProjectRepository
			->shouldReceive('byId')
			->once()
			->andReturn($project);

		$this->mockServerRepository
			->shouldReceive('byId')
			->once();

		$this->mockDeploymentRepository
			->shouldReceive('update')
			->once();

		$mockDeployerFile = $this->mock('App\Services\Deployment\DeployerFile')
			->shouldReceive('getFullPath')
			->once()
			->mock();

		$this->mockDeployerFileDirector
			->shouldReceive('construct')
			->andReturn($mockDeployerFile)
			->times(3);

		$this->mockProcess
			->shouldReceive('run')
			->once();

		$this->mockProcess
			->shouldReceive('isSuccessful')
			->once()
			->andReturn(true);

		$this->mockProcess
			->shouldReceive('getOutput')
			->once();

		$this->mockProcess
			->shouldReceive('getExitCode')
			->once();

		$this->mockProcessBuilder
			->shouldReceive('add')
			->times(7)
			->andReturn($this->mockProcessBuilder);

		$this->mockProcessBuilder
			->shouldReceive('getProcess')
			->once()
			->andReturn($this->mockProcess);

		\Storage::shouldReceive('delete')
			->times(1)
			->andReturn(1);

		$job = new Rollback($deployment);

		$job->handle(
			$this->mockDeploymentRepository,
			$this->mockProjectRepository,
			$this->mockServerRepository,
			$this->mockProcessBuilder
		);
	}

	public function test_Should_Work_When_DeployerIsAbnormalEnd()
	{
		$deployment = Factory::build('App\Models\Deployment', [
			'id'         => 1,
			'project_id' => 1,
			'number'     => 1,
			'task'       => 'deploy',
			'user_id'    => 1,
			'created_at' => new \Carbon\Carbon,
			'updated_at' => new \Carbon\Carbon,
			'user'       => new \App\Models\User,
		]);

		$recipe = Factory::build('App\Models\Recipe', [
			'id'          => 1,
			'name'        => 'Recipe 1',
			'desctiption' => '',
			'body'        => '',
		]);

		$project = Factory::build('App\Models\Project', [
			'id'         => 1,
			'name'       => 'Project 1',
			'recipe_id'  => 1,
			'stage'      => 'staging',
			'created_at' => new \Carbon\Carbon,
			'updated_at' => new \Carbon\Carbon,
			'recipes'    => [$recipe],
		]);

		$this->mockProjectRepository
			->shouldReceive('byId')
			->once()
			->andReturn($project);

		$this->mockServerRepository
			->shouldReceive('byId')
			->once();

		$this->mockDeploymentRepository
			->shouldReceive('update')
			->once();

		$mockDeployerFile = $this->mock('App\Services\Deployment\DeployerFile')
			->shouldReceive('getFullPath')
			->once()
			->mock();

		$this->mockDeployerFileDirector
			->shouldReceive('construct')
			->andReturn($mockDeployerFile)
			->times(3);

		$this->mockProcess
			->shouldReceive('run')
			->once();

		$this->mockProcess
			->shouldReceive('isSuccessful')
			->once()
			->andReturn(false);

		$this->mockProcess
			->shouldReceive('getErrorOutput')
			->once();

		$this->mockProcess
			->shouldReceive('getExitCode')
			->once();

		$this->mockProcessBuilder
			->shouldReceive('add')
			->times(7)
			->andReturn($this->mockProcessBuilder);

		$this->mockProcessBuilder
			->shouldReceive('getProcess')
			->once()
			->andReturn($this->mockProcess);

		\Storage::shouldReceive('delete')
			->times(1)
			->andReturn(1);

		$job = new Rollback($deployment);

		$job->handle(
			$this->mockDeploymentRepository,
			$this->mockProjectRepository,
			$this->mockServerRepository,
			$this->mockProcessBuilder
		);
	}

}
