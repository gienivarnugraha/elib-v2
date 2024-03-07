<?php

namespace Tests\Feature\Resources;

/* This trais is intended to test the actual behavior of the API, using user signed in and perform api request */

trait UseApiTest
{
    public function testUnauthenticatedUserCannotAccessResourceEdnpoints()
    {
        $this->getJson($this->indexEndpoint())->assertUnauthorized();
        $this->getJson($this->showEndpoint(1))->assertUnauthorized();
        $this->postJson($this->createEndpoint())->assertUnauthorized();
        $this->putJson($this->updateEndpoint(1))->assertUnauthorized();
        $this->deleteJson($this->deleteEndpoint(1))->assertUnauthorized();
    }

    public function performTestIndexRequest()
    {
        $this->signIn();

        return $this->getJson($this->indexEndpoint())->assertOk();
    }

    public function performTestCreateRequest(array $data)
    {
        $this->signIn();

        $result = $this->postJson($this->createEndpoint(), $data)
            ->assertCreated()
            ->assertJsonStructure($this->expectedJson);

        return json_decode($result->getContent());
    }

    public function performTestShowRequest($record)
    {
        $this->signIn();

        return $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function performTestUpdateRequest($record, $data)
    {
        $this->signIn();

        $result = $this->putJson($this->updateEndpoint($record), $data)
            // ->assertJsonStructure($this->expectedJson)
            ->assertOk();

        return json_decode($result->getContent());
    }

    public function performTestDeleteRequest($record)
    {
        $this->signIn();

        return $this->deleteJson($this->deleteEndpoint($record))->assertNoContent();
    }

    public function regularAdminPolicy($data)
    {
        $this->asRegularAdmin()->signIn();

        $factory = $this->factory()->create();

        $this->getJson($this->indexEndpoint())->assertOk();
        $this->getJson($this->showEndpoint($factory))->assertOk();
        $this->postJson($this->createEndpoint(), $data)->assertCreated();
        $this->putJson($this->updateEndpoint($factory), $data)->assertOk();

        // TODO: POLICY CHECK STILL ERROR
        $this->deleteJson($this->deleteEndpoint($factory))->assertForbidden();
    }

    protected function performTestTableRequestWithAllFields()
    {
        $this->signIn();

        $attributes = $this->tableRequest()
            ->resolveTable()
            ->getColumns()
            ->map(fn ($column) => $column->attribute) //$column->isRelation() ? $column->getQualifiedName() :
            ->all();

        $this->factory()->count(5)->create();

        $this->getJson($this->tableEndpoint())
            ->assertOk()
            ->assertJsonCount($this->model()->count(), 'data')
            ->assertJsonStructure([
                'data' => [$attributes],
            ]);
    }
}
